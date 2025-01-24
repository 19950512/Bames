<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Aplicacao\Comandos\Clientes\ConsultarInformacoesNaInternet\LidarConsultarInformacoesNaInternet;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\ConsultarInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\RepositorioConsultarInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Infraestrutura\Adaptadores\ConsultarInformacoesNaInternet\ImplementacaoEscavadorConsultarInformacoesNaInternet;
use App\Infraestrutura\Repositorios\Clientes\ImplementacaoRepositorioClientes;
use App\Infraestrutura\Repositorios\ConsultarInformacoesNaInternet\ImplementacaoConsultarInformacoesNaInternet;
use Exception;
use DI\Container;
use PDO;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioConsultarInformacoesNaInternet::class => function(Container $container)
    {
        return new ImplementacaoConsultarInformacoesNaInternet(
            pdo: $container->get(PDO::class),
        );
    },
    LidarConsultarInformacoesNaInternet::class => function(Container $container)
    {
        return new LidarConsultarInformacoesNaInternet(
            consultarInformacoesNaInternet: $container->get(ConsultarInformacoesNaInternet::class),
            repositorioClientes: $container->get(RepositorioClientes::class),
            repositorioConsultarInformacoesNaInternet: $container->get(RepositorioConsultarInformacoesNaInternet::class),
            repositorioEmpresa: $container->get(RepositorioEmpresa::class),
            container: $container,
        );
    },
    ConsultarInformacoesNaInternet::class => function(Container $container)
    {
        return new ImplementacaoEscavadorConsultarInformacoesNaInternet(
            ambiente: $container->get(Ambiente::class),
        );
    },
];
