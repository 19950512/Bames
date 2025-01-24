<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Aplicacao\Comandos\Processos\LidarConsultarProcessoPorOAB;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Infraestrutura\Repositorios\ContaBancaria\ImplementacaoContaBancaria;
use PDO;
use Exception;
use DI\Container;
use App\Dominio\Entidades\JusiziEntity;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Infraestrutura\Repositorios\Empresa\ImplementacaoRepositorioEmpresa;
use App\Infraestrutura\Repositorios\Autenticacao\ImplementacaoRepositorioAutenticacao;
use App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa\ImplementacaoLidarCadastrarEmpresa;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    RepositorioContaBancaria::class => function(Container $container)
    {
        return new ImplementacaoContaBancaria(
            pdo: $container->get(PDO::class),
        );
    },
];
