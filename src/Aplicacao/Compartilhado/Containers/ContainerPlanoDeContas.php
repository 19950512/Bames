<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use App\Infraestrutura\Repositorios\Modelos\ImplementacaoRepositorioModelos;
use App\Infraestrutura\Repositorios\PlanoDeContas\ImplementacaoRepositorioPlanoDeContas;
use Exception;
use DI\Container;
use PDO;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioPlanoDeContas::class => function(Container $container)
    {
        return new ImplementacaoRepositorioPlanoDeContas(
            pdo: $container->get(PDO::class),
        );
    },
];
