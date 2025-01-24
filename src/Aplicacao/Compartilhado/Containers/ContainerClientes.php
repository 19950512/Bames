<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Infraestrutura\Repositorios\Clientes\ImplementacaoRepositorioClientes;
use Exception;
use DI\Container;
use PDO;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioClientes::class => function(Container $container)
    {
        return new ImplementacaoRepositorioClientes(
            pdo: $container->get(PDO::class),
        );
    },
];
