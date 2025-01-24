<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Compartilhado\Data\Data;
use App\Aplicacao\Compartilhado\Data\ImplementacaoData;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Docx\Docx;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Aplicacao\Compartilhado\Notificacao\Notificacao;
use App\Infraestrutura\Adaptadores\Conversor\ImplementacaoAPIConversorDeArquivo;
use App\Infraestrutura\Adaptadores\Conversor\ImplementacaoDoc2PDFShellConversorDeArquivo;
use App\Infraestrutura\Adaptadores\Discord\ImplementacaoDiscord;
use App\Infraestrutura\Adaptadores\Docx\ImplementacaoDocx;
use App\Infraestrutura\Adaptadores\GerenciadorDeArquivos\ImplementacaoR2GerenciadorDeArquivos;
use App\Infraestrutura\Adaptadores\Mensageria\ImplementacaoMensageriaRabbitMQ;
use App\Infraestrutura\Adaptadores\Notificacao\ImplementacaoNotificacaoFirebase;
use PDO;
use Exception;
use DI\Container;
use PDOException;
use App\Aplicacao\Compartilhado\Token;
use App\Dominio\Entidades\JusiziEntity;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Aplicacao\Compartilhado\Agenda\Agenda;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Infraestrutura\Adaptadores\Email\PHPMailerEmail;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Infraestrutura\Adaptadores\Cache\ImplementacaoCacheRedis;
use App\Infraestrutura\Adaptadores\Agenda\ImplementacaoGoogleAgenda;
use App\Infraestrutura\Adaptadores\Token\ImplementacaoTokenFirebaseJWT;
use App\Infraestrutura\Repositorios\Email\ImplementacaoRepositorioEmail;
use App\Infraestrutura\Adaptadores\Ambiente\ImplementacaoAmbienteArquivo;
use App\Infraestrutura\Repositorios\Agenda\ImplementacaoRepositorioAgenda;
use App\Infraestrutura\Repositorios\Request\ImplementacaoRepositorioRequest;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

return [
    Ambiente::class => \DI\create(ImplementacaoAmbienteArquivo::class),
    JusiziEntity::class => function(Container $container)
    {
        return new JusiziEntity(
            fantasia: 'Bames',
            responsavelNome: 'Matheus Maydana',
            emailComercial: 'contato@bames.com.br',
            responsavelCargo: 'CTO - Chief Technology Officer'
        );
    },
    Data::class => function(Container $container)
    {
        return new ImplementacaoData();
    },
    Discord::class => function(Container $container)
    {
        return new ImplementacaoDiscord(
            ambiente: $container->get(Ambiente::class)
        );
    },
    Cache::class => function(Container $content)
    {
        return new ImplementacaoCacheRedis(
            ambiente: $content->get(Ambiente::class)
        );
    },
    Notificacao::class => function(Container $container)
    {
        return new ImplementacaoNotificacaoFirebase(
            ambiente: $container->get(Ambiente::class)
        );
    },
    Mensageria::class => function(Container $container)
    {
        return new ImplementacaoMensageriaRabbitMQ(
            host: $container->get('EVENT_BUS_HOST'),
            ambiente: $container->get(Ambiente::class),
        );
    },
    GerenciadorDeArquivos::class => function(Container $container)
    {
        return new ImplementacaoR2GerenciadorDeArquivos(
            ambiente: $container->get(Ambiente::class)
        );
    },
    ConversorDeArquivo::class => function(Container $container)
    {
        return new ImplementacaoDoc2PDFShellConversorDeArquivo(
            discord: $container->get(Discord::class)
        );

        /*
        return new ImplementacaoAPIConversorDeArquivo(
            ambiente: $container->get(Ambiente::class)
        );
        */
    },
	PDO::class => function(Container $content)
    {

        $env = $content->get(Ambiente::class);

        try {

            //$linkConexao = "pgsql:host={$env::get('DB_HOST')};dbname={$env::get('DB_DATABASE')};user={$env::get('DB_USERNAME')};password={$env::get('DB_PASSWORD')};port={$env::get('DB_PORT')}";
            $linkConexao = "pgsql:host={$content->get('DB_HOST')};dbname={$env::get('DB_DATABASE')};user={$env::get('DB_USERNAME')};password={$env::get('DB_PASSWORD')};port={$content->get('DB_PORT')}";

            $PDO = new PDO($linkConexao);
            $PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $PDO;
        }catch (PDOException $erro){

            $message = $erro->getMessage();

            if($message == 'could not find driver'){
                die('Não foi encontrado o Driver do PDO.');
            }

            header("HTTP/1.0 500 Connection");
            echo str_replace('{{mensagem}}', $message, file_get_contents(__DIR__.'/sem_conexao.html'));
            exit;
        }
    },
    Docx::class => function(Container $container)
    {
        return new ImplementacaoDocx(
            data: $container->get(Data::class),
        );
    },
    Email::class => function(Container $container)
    {
        return new PHPMailerEmail(
            ambiente: $container->get(Ambiente::class)
        );
    },
    RepositorioEmail::class => function(Container $container)
    {
        return new ImplementacaoRepositorioEmail(
            pdo: $container->get(PDO::class)
        );
    },
    RepositorioRequest::class => function(Container $container)
    {
        return new ImplementacaoRepositorioRequest(
            pdo: $container->get(PDO::class)
        );
    },
    Token::class => function(Container $container)
    {
        return new ImplementacaoTokenFirebaseJWT(
            ambiente: $container->get(Ambiente::class)
        );
    },
    Agenda::class => function(Container $container)
    {
        return new ImplementacaoGoogleAgenda(
            ambiente: $container->get(Ambiente::class),
            cache: $container->get(Cache::class)
        );
    },
    RepositorioAgenda::class => function(Container $container)
    {
        return new ImplementacaoRepositorioAgenda(
            pdo: $container->get(PDO::class)
        );
    }
];