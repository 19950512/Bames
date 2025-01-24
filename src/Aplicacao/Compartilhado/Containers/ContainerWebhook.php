<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Dominio\Repositorios\Webhook\RepositorioWebhook;
use App\Infraestrutura\Repositorios\Webhook\ImplementacaoRepositorioWebhook;
use Exception;
use DI\Container;
use PDO;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioWebhook::class => function(Container $container)
    {
        return new ImplementacaoRepositorioWebhook(
            pdo: $container->get(PDO::class),
        );
    },
];
