<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\ContaBancaria\Enumerados\Banco;
use App\Dominio\Entidades\JusiziEntity;
use App\Infraestrutura\Adaptadores\PlataformaDeCobranca\Asaas\ImplementacaoAsaasPlataformaDeCobranca;
use App\Infraestrutura\Adaptadores\PlataformaDeCobranca\ImplementacaoNenhumPlataformaDeCobranca;
use PDO;
use Exception;
use DI\Container;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    'ImplementacaoDoBancoASAAS' => function(Container $container)
    {
        return new ImplementacaoAsaasPlataformaDeCobranca(
            discord: $container->get(Discord::class),
            ambiente: $container->get(Ambiente::class),
        );
    },
    'ImplementacaoNenhumBanco' => function(Container $container)
    {
        return new ImplementacaoNenhumPlataformaDeCobranca();
    },
];
