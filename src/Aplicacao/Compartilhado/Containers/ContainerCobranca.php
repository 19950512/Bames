<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Infraestrutura\Repositorios\Cobranca\ImplementacaoRepositorioCobranca;
use PDO;
use Exception;
use DI\Container;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioCobranca::class => function(Container $container)
    {
        return new ImplementacaoRepositorioCobranca(
            pdo: $container->get(PDO::class),
        );
    },
];
