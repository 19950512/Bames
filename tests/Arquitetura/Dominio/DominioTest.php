<?php

arch('Dominio não pode utilizar nada de nenhuma outra camada')
    ->expect('App\Dominio')
    ->not->toUse([
        'App\Infraestrutura',
        'App\Aplicacao',
    ])
    ->ignoring([
        'App\Aplicacao\Compartilhado'
    ]);