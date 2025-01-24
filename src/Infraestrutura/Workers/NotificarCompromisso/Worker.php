#!/usr/bin/php
<?php

declare(strict_types=1);

use DI\Container;
use PhpAmqpLib\Message\AMQPMessage;
use App\Infraestrutura\Workers\Workers;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Comandos\Agenda\NotificarCompromisso\Resposta;
use App\Aplicacao\Comandos\Agenda\NotificarCompromisso\LidarNotificarCompromisso;
use App\Aplicacao\Comandos\Agenda\NotificarCompromisso\ComandoNotificarCompromisso;

$pathContainer = __DIR__.'/../../../Aplicacao/Compartilhado/Containers/Container.php';
if(!is_file($pathContainer)){
    echo "O arquivo $pathContainer não existe.";
    return;
}

require_once $pathContainer;

$worker = new Workers(
    evento: Evento::NotificarCompromissos,
    maximoDeTentativasDeProcessamento: 3,
    lidarComMensagem: function(Container $container, AMQPMessage $mensagem){

        echo "mensagem: ".$mensagem->getBody()."\n";

        if(!json_validate($mensagem->getBody())){
            echo "A mensagem precisa ser um JSON\n";
            $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
            return;
        }

        $parametros = json_decode($mensagem->getBody(), true);

        try {

            $comando = new ComandoNotificarCompromisso(
                codigoEvento: (string) ($parametros['codigoEvento'] ?? ''),
                empresaCodigo: (string) ($parametros['empresaCodigo'] ?? '')
            );

            $comando->executar();

            $resposta = $container->get(LidarNotificarCompromisso::class)->lidar($comando);

            if($resposta == Resposta::NAO_ENCONTRADO){
                echo "Evento não encontrado!\n";
                $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
                return;
            }

            if($resposta == Resposta::JA_PASSOU){
                echo "O evento já passou!\n";
                $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
                return;
            }

            if($resposta == Resposta::AINDA_NAO_E_HORA){
                echo "Ainda não é hora de notificar o evento!\n";
                return;
            }

            echo "Evento notificado com sucesso!\n";
            $mensagem->getChannel()->basic_ack($mensagem->getDeliveryTag());

        }catch (Exception $erro){
            echo "{$erro->getMessage()}\n";

            throw new Exception($erro->getMessage());
        }
    }
);

$worker->start();