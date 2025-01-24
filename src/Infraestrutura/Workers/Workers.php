<?php

declare(strict_types=1);

namespace App\Infraestrutura\Workers;

use App\Aplicacao\Compartilhado\Containers\Container AS ContainerAPP;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use \Di\Container;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class Workers
{
    private Container $container;

    public function __construct(
        private Evento $evento,
        private int $maximoDeTentativasDeProcessamento,
        private $lidarComMensagem,
    ){
        $pathContainer = __DIR__.'/../../Aplicacao/Compartilhado/Containers/Container.php';
        if(!is_file($pathContainer)){
            echo $this->momento()." | O arquivo $pathContainer não existe.\n";
            return;
        }

        require_once $pathContainer;

        $containerApp = ContainerAPP::getInstance();

        $pathFileEnv = __DIR__.'/../../../.env';
        if(!is_file($pathFileEnv)){
            echo $this->momento()." | O arquivo $pathFileEnv não existe.";
            return;
        }

        $env = file_get_contents($pathFileEnv);
        $env = explode("\n", $env);

        foreach($env as $line){
            $line = explode('=', $line);
            if($line[0] == 'DB_HOST'){
                $dbhost = $line[1];
                break;
            }
        }

        $this->container = $containerApp->get([
            'DB_HOST' => $dbhost
        ]);

        echo $this->momento()." | Container iniciado.\n";

        $this->maximoDeTentativasDeProcessamento = max($this->maximoDeTentativasDeProcessamento, 0);
    }

    private function momento(): string
    {
        return date('d/m/Y H:i:s');
    }

    private function processarComRetry(AMQPMessage $mensagem): void
    {
        $tentativas = 0;
        $sucesso = false;
        $maxTentativas = $this->maximoDeTentativasDeProcessamento;

        // Política de Retry: Tenta processar a mensagem até o limite de tentativas
        while ($tentativas <= $maxTentativas && !$sucesso) {
            try {
                echo $this->momento() . " | Tentativa " . ($tentativas + 1) . " de " . ($maxTentativas + 1) . "\n";

                ($this->lidarComMensagem)($this->container, $mensagem);

                $sucesso = true;
                echo $this->momento() . " | Processamento concluído com sucesso.\n";

                $mensagem->getChannel()->basic_ack($mensagem->getDeliveryTag());

            } catch (Exception $erro) {
                $tentativas++;
                echo $this->momento() . " | Erro ao processar a mensagem: " . $erro->getMessage() . "\n";

                // Se atingiu o limite de tentativas, não tenta mais
                if ($tentativas > $maxTentativas) {
                    echo $this->momento() . " | Máximo de tentativas alcançado. Abortando o processamento.\n";
                    $this->container->get(Discord::class)->enviar(
                        canaldeTexto: CanalDeTexto::Exceptions,
                        mensagem: 'Erro ao processar mensagem para o evento '.$this->evento->value.PHP_EOL.'Erro: '.$erro->getMessage()
                    );
                    $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
                    return;
                }

                // Backoff exponencial: espera antes de tentar novamente
                $tempoDeEspera = pow(2, $tentativas) * 1000000; // Em microsegundos (por exemplo, 2, 4, 8, 16 segundos)
                echo $this->momento() . " | Esperando " . ($tempoDeEspera / 1000000) . " segundos antes da próxima tentativa.\n";
                usleep($tempoDeEspera);
            }
        }
    }

    public function start(): void
    {
        try {
            echo $this->momento()." | Estamos prontos para receber mensagens da fila ".$this->evento->value."\n";
            echo $this->momento()." | Configurada com $this->maximoDeTentativasDeProcessamento tentativas máxima de processamento.\n";

            $this->container->get(Discord::class)->enviar(
                canaldeTexto: CanalDeTexto::Workers,
                mensagem: "O Worker {$this->evento->value} está pronto para receber mensagens da fila.\n"
            );

            $this->container->get(Mensageria::class)->inscrever(
                evento: $this->evento,
                retrochamada: function(AMQPMessage $mensagem) {
                    echo $this->momento()." | Recebemos uma mensagem\n";
                    $this->processarComRetry($mensagem); // Chama o método de processamento com retry
                }
            );

        } catch (Exception $erro) {
            echo $this->momento()." | Ops, a fila caiu\n";
            echo $erro->getMessage()."\n";

            $this->container->get(Discord::class)->enviar(
                canaldeTexto: CanalDeTexto::Exceptions,
                mensagem: 'Ops, a fila caiu '.$this->evento->value. PHP_EOL.$erro->getMessage()
            );
        }
    }
}

