#!/usr/bin/php
<?php

declare(strict_types=1);

use DI\Container;
use PhpAmqpLib\Message\AMQPMessage;
use App\Infraestrutura\Workers\Workers;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\LidarNovoEventoWorker;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\ComandoNovoEventoWorker;

$pathContainer = __DIR__.'/../../../../Aplicacao/Compartilhado/Containers/Container.php';
if(!is_file($pathContainer)){
    echo "O arquivo $pathContainer não existe.";
    return;
}

require_once $pathContainer;

$worker = new Workers(
    evento: Evento::NovoEventoAgenda,
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

            $comando = new ComandoNovoEventoWorker(
                titulo: $parametros['titulo'] ?? '',
                descricao: $parametros['descricao'] ?? '',
                diaTodo: (bool) ($parametros['diaTodo'] ?? false),
                recorrencia: (int) ($parametros['recorrencia'] ?? 0),
                horarioEventoInicio: $parametros['horarioEventoInicio'] ?? '',
                horarioEventoFim: $parametros['horarioEventoFim'] ?? '',
                empresaCodigo: $parametros['empresaCodigo'] ?? '',
                usuarioCodigo: $parametros['usuarioCodigo'] ?? '',
                notificarPorEmail: (bool) ($parametros['notificarPorEmail'] ?? false),
            );

            $comando->executar();

            $container->get(LidarNovoEventoWorker::class)->lidar($comando);

            echo "Evento criado com sucesso!\n";
            //$mensagem->getChannel()->basic_ack($mensagem->getDeliveryTag());
            return;

        }catch (Exception $erro){

            echo "{$erro->getMessage()}\n";

            throw new Exception($erro->getMessage());
        }
    }
);

$worker->start();