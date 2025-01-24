#!/usr/bin/php
<?php

declare(strict_types=1);

use App\Aplicacao\Compartilhado\Email\Email;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Infraestrutura\Workers\Workers;
use DI\Container;
use PhpAmqpLib\Message\AMQPMessage;

$pathContainer = __DIR__.'/../../../Aplicacao/Compartilhado/Containers/Container.php';
if(!is_file($pathContainer)){
    echo "O arquivo $pathContainer não existe.";
    return;
}

require_once $pathContainer;

$worker = new Workers(
    evento: Evento::EnviarEmail,
    maximoDeTentativasDeProcessamento: 10,
    lidarComMensagem: function(Container $container, AMQPMessage $mensagem){

        echo "mensagem: ".$mensagem->getBody()."\n";

        if(!json_validate($mensagem->getBody())){
            echo "A mensagem precisa ser um JSON\n";
            $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
            return;
        }

        $parametros = json_decode($mensagem->getBody(), true);

        $parametrosEmail = new EntradaFronteiraEnviarEmail(
            destinatarioEmail: (string) ($parametros['destinatarioEmail'] ?? ''),
            destinatarioNome: (string) ($parametros['destinatarioNome'] ?? ''),
            assunto: (string) ($parametros['assunto'] ?? ''),
            mensagem: (string) ($parametros['mensagem'] ?? ''),
        );

        try {

            $respostaEmail = $container->get(Email::class)->enviar(
                params: $parametrosEmail
            );

            echo "Email código: $respostaEmail->emailCodigo\n";

            echo "finalizada.\n";

        }catch (Exception $erro){

            throw new Exception($erro->getMessage());
        }
    }
);

$worker->start();