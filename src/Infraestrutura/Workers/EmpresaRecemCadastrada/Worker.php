#!/usr/bin/php
<?php

declare(strict_types=1);

use App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa\ComandoPosCadastrarEmpresa;
use App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa\LidarPosCadastrarEmpresa;
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
    evento: Evento::EmpresaRecemCadastradaNoSistema,
    maximoDeTentativasDeProcessamento: 1,
    lidarComMensagem: function(Container $container, AMQPMessage $mensagem){

        echo "mensagem: ".$mensagem->getBody()."\n";

        if(!json_validate($mensagem->getBody())){
            echo "A mensagem precisa ser um JSON\n";
            $mensagem->getChannel()->basic_nack($mensagem->getDeliveryTag());
            return;
        }

        $parametros = json_decode($mensagem->getBody(), true);

        try {

            $comando = new ComandoPosCadastrarEmpresa(
                    empresaCodigo: (string) ($parametros['empresaCodigo'] ?? '')
            );

            $comando->executar();

            $container->get(LidarPosCadastrarEmpresa::class)->lidar($comando);

            echo "Empresa Recem Cadastrada processado com sucesso!\n";

        }catch (Exception $erro){
            echo "{$erro->getMessage()}\n";

            throw new Exception($erro->getMessage());
        }
    }
);

$worker->start();