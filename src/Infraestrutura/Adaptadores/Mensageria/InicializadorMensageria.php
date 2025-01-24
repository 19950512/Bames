#!/usr/bin/php
<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Mensageria;

use App\Aplicacao\Compartilhado\Containers\Container;

function momento(){
  return date('d/m/Y H:i:s');
}

$pathContainer = __DIR__.'/../../../Aplicacao/Compartilhado/Containers/Container.php';
if(!is_file($pathContainer)){
    echo momento()." | O arquivo $pathContainer não existe.";
    return;
}

require_once $pathContainer;

echo momento()." | Iniciando o script Inicializador de Mensageria\n";

$containerApp = Container::getInstance();

$pathFileEnv = __DIR__.'/../../../../.env';
if(!is_file($pathFileEnv)){
    echo momento()." | O arquivo $pathFileEnv não existe.";
    return;
}

$env = file_get_contents($pathFileEnv);
$env = explode("\n", $env);

foreach($env as $line){
    $line = explode('=', $line);
    if($line[0] == 'DB_HOST'){
        $dbhost = $line[1];
        break;
    }
}

$container = $containerApp->get([
    'DB_HOST' => $dbhost
]);

echo momento()." | Container iniciado.\n";

$mensageria = $container->get(Mensageria::class);

echo momento()." | Iniciando criação das filas.\n";
$mensageria->criarFilas();
echo momento()." | Finalização da criação das filas.\n";
