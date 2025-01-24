#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\BotDiscord;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Leituras\BotDiscord\Empresas;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraEmpresa;
use Exception;
use App\Aplicacao\Compartilhado\Containers\Container;

$pathAutoload = __DIR__."/../../../../vendor/autoload.php";
if(!is_file($pathAutoload)){
    throw new Exception("Execute composer install");
}
include $pathAutoload;
$pathAutoloadSistema = __DIR__."/../../../Aplicacao/Compartilhado/Containers/autoload.php";
include $pathAutoloadSistema;

$containerApp = Container::getInstance();

$pathFileEnv = __DIR__.'/../../../../.env';
$dbhost = '';
if(is_file($pathFileEnv)){

    $env = file_get_contents($pathFileEnv);
    $env = explode("\n", $env);

    foreach($env as $line){
        $line = explode('=', $line);
        if($line[0] == 'DB_HOST'){
            $dbhost = $line[1];
            break;
        }
    }
}

$container = $containerApp->get([
    'DB_HOST' => $dbhost,
]);

$entidadeEmpresarialData = new SaidaFronteiraEmpresa(
    empresaCodigo: (new IdentificacaoUnica())->get(),
    nome: 'BOT Discord',
    numeroDocumento: CNPJ::gerar(),
    responsavelCodigo: (new IdentificacaoUnica())->get(),
    responsavelOAB: 'RS 123456',
    responsavelNomeCompleto: 'Matheus Maydana',
    responsavelEmail: 'mattmaydana@gmail.com',
    acessoNaoAutorizado: false,
    acessoNaoAutorizadoMotivo: '',
    acessoTotalAutorizadoPorMatheusMaydana: true,
);
$entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($entidadeEmpresarialData);

$container->set(EntidadeEmpresarial::class, $entidadeEmpresarial);

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;

$ambiente = $container->get(Ambiente::class);

$discord = new Discord([
    'token' => $ambiente->get('DISCORD_BOT_TOKEN'), // Put your Bot token here from https://discord.com/developers/applications/
    'intents' => Intents::getDefaultIntents()
]);

$discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use (&$container){

    if ($message->author->bot) {
        return;
    }

    echo "{$message->author->username}: {$message->content}", PHP_EOL;

    similar_text(mb_strtolower($message->content), 'quantos clientes temos hoje?', $match);

    echo "Match: $match\n";

    if($match > 95){
        $totalClientes = $container->get(Empresas::class)->totalClientes();

        $clientePlural = "s";
        if($totalClientes <= 0){
            $totalClientes = "nenhum";
            $clientePlural = "";
        }

        $message->reply("Hoje temos, {$totalClientes} cliente{$clientePlural} na plataforma.");
    }




    similar_text(mb_strtolower($message->content), 'informações detalhadas de todos os clientes', $match);

    if($match > 95){

        $clientes = $container->get(Empresas::class)->totalClientesDetalhado();

        $conteudo = "";
        foreach ($clientes as $key => $empresa){

            $key++;

            $cep = '';
            if(isset($empresa['cep']) and !empty($empresa['cep'])){
                $cep = "- CEP: {$empresa['cep']}".PHP_EOL;
            }

            $cidade = '';
            if(isset($empresa['cidade']) and !empty($empresa['cidade'])){
                $cidade = "- Cidade: {$empresa['cidade']}".PHP_EOL;
            }

            $estado = '';
            if(isset($empresa['estado']) and !empty($empresa['estado'])){
                $estado = "- Estado: {$empresa['estado']}".PHP_EOL;
            }

            $whatsapp = '';
            if(isset($empresa['whatsapp']) and !empty($empresa['whatsapp'])){
                $whatsapp = "- Whatsapp: {$empresa['whatsapp']}".PHP_EOL;
            }


            $conteudo .= <<<conteudoo
            
            **Empresa - {$key} **
            ```bash
            - Código: {$empresa['codigo']}
            - Nome da Empresa: {$empresa['nome']}
            - Documento: {$empresa['documento']}
            - E-mail: {$empresa['email']}{$whatsapp}{$cidade}{$estado}{$cep}
            - Data de Cadastro: {$empresa['cadastro']}
            ```
            conteudoo;

        }

        $message->reply($conteudo);
    }

});

$discord->run();