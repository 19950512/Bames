<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Webhook;


use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma\ComandoBoletoFoiAceitoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma\LidarBoletoFoiAceitoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiPagoNaPlataforma\ComandoBoletoFoiPagoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiPagoNaPlataforma\LidarBoletoFoiPagoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\SalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema\ComandoSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema;
use App\Aplicacao\Comandos\Cobranca\SalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema\LidarSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Webhook\Enums\Parceiro;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Webhook\Fronteiras\EntradaFronteiraSalvarWebhook;
use App\Dominio\Repositorios\Webhook\RepositorioWebhook;
use DateTime;
use DI\Container;
use Exception;

final readonly class LidarReceberWebhook implements Lidar
{

    public function __construct(
        private RepositorioWebhook $repositorioWebhook,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private Ambiente $ambiente,
        private Discord $discord,
        private Container $container
    ){}

    public function lidar(Comando $comando): true
    {
        if (!is_a($comando, ComandoReceberWebhook::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $payload = $comando->obterPayloadPronto();
        $headers = $comando->obterHeadersPronto();
        $ip = $comando->obterIpPronto();
        $userAgent = $comando->obterUserAgentPronto();
        $metodo = $comando->obterMetodoPronto();
        $uri = $comando->obterUriPronto();
        $parceiro = Parceiro::tryFrom($comando->obterParceiroPronto());

        $headers = array_change_key_case($headers, CASE_LOWER);

        // Remove a parte do URL que está antes de '?' (se necessário)
        $queryString = parse_url($uri, PHP_URL_QUERY);

        // Parse os parâmetros da query string
        parse_str($queryString, $params);

        $empresaCodigo = $params['empresaCodigo'] ?? '';
        $codigoContaBancaria = $params['contaBancariaCodigo'] ?? '';

        if($parceiro === Parceiro::Escavador){
            // Vamos validar se o header contém o token de autorização (authorization) = X. Esse token é gerado pelo Escavador no painel da API
            if(!array_key_exists('authorization', $headers)){
                throw new Exception("Ops, não autorizado.");
            }

            if($headers['authorization'] !== $this->ambiente->get('API_ESCAVADOR_WEBHOOK_AUTHORIZATION')){
                throw new Exception("Ops, não autorizado.");
            }
        }

        if($parceiro == Parceiro::Asaas){
            // Vamos validar se o header contém o token de autorização (asaas-access-token) = X. Esse token é gerado no momento que o webhook é configurado e é salvo no banco de dados para validação futura.
            if(!array_key_exists('asaas-access-token', $headers)){
                throw new Exception("Ops, não autorizado.");
            }

            // Vamos verificar se o token existe no banco de dados e se corresponde a conta bancaria informada.
            if(!$this->repositorioContaBancaria->verificaAuthenticidadeWebhookAsaas(
                contaBancariaCodigo: $codigoContaBancaria,
                empresaCodigo: $empresaCodigo,
                webhookCodigo: (string) ($headers['asaas-access-token'] ?? ''),
            )){
                throw new Exception("Ops, não autorizado! Seu danadinho.");
            }
        }

        if($parceiro == Parceiro::Asaas){
            if($this->repositorioWebhook->verificarWebhookRecebido(
                eventID: $payload['id'] ?? '',
            )){
                throw new Exception("Ops, já recebemos esse Webhook.");
            }
        }

        try {

            $parametrosSalvarWebhook = new EntradaFronteiraSalvarWebhook(
                eventID: $payload['id'] ?? '',
                payload: json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                headers: json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ip: $ip,
                userAgent: $userAgent,
                metodo: $metodo,
                uri: $uri,
                parceiro: $parceiro->value,
                momento: (new DateTime('now'))->format('Y-m-d H:i:s')
            );

            $this->repositorioWebhook->salvarWebhook($parametrosSalvarWebhook);

            $this->discord->enviar(CanalDeTexto::Webhook, "Webhook recebido com sucesso de {$parceiro->value}!");
            $this->discord->enviar(CanalDeTexto::Webhook, "Payload: ```json\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "```");

        } catch (Exception $e) {
            throw new Exception("Ops, não consegui salvar o Webhook - {$e->getMessage()}");
        }

        // Aqui poderá ser implementado um envio para a fila de processamento (RabbitMQ)

        if($parceiro == Parceiro::Asaas){

            if(isset($payload['event']) && $payload['event'] == 'PAYMENT_CREATED'){

                if(isset($payload['payment']['installment']) && is_string($payload['payment']['installment']) && !empty($payload['payment']['installment'])){

                    try {
                        $parcelamentoCodigo = $payload['payment']['installment'];

                        $comando = new ComandoSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema(
                            empresaCodigo: $empresaCodigo,
                            contaBancariaCodigo: $codigoContaBancaria,
                            codigoParcelamentoNaPlataformaDeCobranca: $parcelamentoCodigo
                        );
                        $comando->executar();

                    } catch (Exception $e) {
                        throw new Exception("Ops, parametros invalidos para o Webhook - Comando PAYMENT_CREATED - {$e->getMessage()}");
                    }

                    try {
                        $this->container->get(LidarSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema::class)->lidar($comando);
                    }catch (Exception $e) {
                        throw new Exception("Ops, não consegui lidar com o Webhook - Lidar - {$e->getMessage()}");
                    }
                    return true;
                }


                /// EU ACREDITO QUE DARIA para fazer esse rolê sem pedir o código da empresa, ficaria mais elegante. Mas, como não tenho certeza, vou deixar assim.
                /// E isso também facilita a vida do desenvolvedor que está consumindo a API, pois ele não precisa ficar fazendo malabarismos para pegar o código da empresa. Mas daria para fazer sem pedir o código da empresa.
                if(empty($empresaCodigo)){
                    throw new Exception("Ops, parametros invalidos para o Webhook, informe o código da empresa.");
                }
                try {

                    $comando = new ComandoBoletoFoiAceitoNaPlataforma(
                        empresaCodigo: $empresaCodigo,
                        boletoCodigoNaPlataforma:(string) $payload['payment']['id'],
                    );

                    $comando->executar();

                }catch (Exception $e) {
                    throw new Exception("Ops, parametros invalidos para o Webhook - Comando PAYMENT_CREATED - {$e->getMessage()}");
                }

                try {
                    $this->container->get(LidarBoletoFoiAceitoNaPlataforma::class)->lidar($comando);

                    // Vamos ver se esse hook vem com instrucao de que esse boleto faz parte de uma parcelamento
                    // se fizer, vamos consultar o parcelamento e pegar todos os boletos gerados pelo parcelamento e inserir no sistema caso não exista pelo paymentID
                }catch (Exception $e) {
                    throw new Exception("Ops, não consegui lidar com o Webhook - {$e->getMessage()}");
                }

                return true;
            }
            if(isset($payload['event']) && $payload['event'] == 'PAYMENT_RECEIVED'){

                /// EU ACREDITO QUE DARIA para fazer esse rolê sem pedir o código da empresa, ficaria mais elegante. Mas, como não tenho certeza, vou deixar assim.
                /// E isso também facilita a vida do desenvolvedor que está consumindo a API, pois ele não precisa ficar fazendo malabarismos para pegar o código da empresa. Mas daria para fazer sem pedir o código da empresa.
                if(empty($empresaCodigo)){
                    throw new Exception("Ops, parametros invalidos para o Webhook, informe o código da empresa.");
                }

                try {

                    $comando = new ComandoBoletoFoiPagoNaPlataforma(
                        empresaCodigo: $empresaCodigo,
                        boletoCodigoNaPlataforma:(string) $payload['payment']['id'],
                        dataPagamento:(string) $payload['payment']['paymentDate'],
                        valorRecebido: (float) $payload['payment']['netValue']
                    );

                    $comando->executar();

                }catch (Exception $e) {
                    throw new Exception("Ops, parametros invalidos para o Webhook - Comando PAYMENT_RECEIVED - {$e->getMessage()}");
                }

                try {
                    $this->container->get(LidarBoletoFoiPagoNaPlataforma::class)->lidar($comando);
                }catch (Exception $e) {

                }

                return true;
            }
        }
        return true;
    }
}