<?php

use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Authorization;

$blackListFunctions = [
    'dd',
    'dump',
    'var_dump',
    'print_r',
    'exit'
];

arch('Não podem haver '. implode(', ', $blackListFunctions) .' no código')
    ->expect($blackListFunctions)
    ->not->toBeUsed()
    ->ignoring([
        Authorization::class,
        Controller::class
    ]);

arch('Todas as classes devem utilizar StrictTypes')
    ->expect('App')
    ->toUseStrictTypes();