<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Aplicacao\Comandos\Clientes\ConsultarProcessos\LidarConsultaProcessosDoCliente;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\RepositorioConsultarInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;
use Exception;
use DI\Container;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
	LidarConsultaProcessosDoCliente::class => function(Container $container)
    {
        return new LidarConsultaProcessosDoCliente(
            consultaDeProcesso: $container->get(ConsultaDeProcesso::class),
            repositorioConsultaDeProcesso: $container->get(RepositorioConsultaDeProcesso::class),
            repositorioCliente: $container->get(RepositorioClientes::class),
            repositorioEmpresa: $container->get(RepositorioEmpresa::class),
            repositorioConsultarInformacoesNaInternet: $container->get(RepositorioConsultarInformacoesNaInternet::class),
            discord: $container->get(Discord::class),
            cache: $container->get(Cache::class),
        );
    },
];
