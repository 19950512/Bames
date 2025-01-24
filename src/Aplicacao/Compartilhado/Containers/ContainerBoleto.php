<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Infraestrutura\Repositorios\Boleto\ImplementacaoRepositorioBoleto;
use PDO;
use Exception;
use DI\Container;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioBoleto::class => function(Container $container)
    {
        return new ImplementacaoRepositorioBoleto(
            pdo: $container->get(PDO::class),
        );
    },
];
