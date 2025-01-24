<?php

use App\Infraestrutura\Adaptadores\ConsultarInformacoesNaInternet\ImplementacaoEscavadorConsultarInformacoesNaInternet;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Authorization;
use App\Infraestrutura\Repositorios\ConsultarInformacoesNaInternet\ImplementacaoConsultarInformacoesNaInternet;

arch('Infraestrutura não pode utilizar nada de nenhuma outra camada')
	->expect('App\Infraestrutura')
	->not->toBeUsedIn([
		'App\Dominio',
		'App\Configuracao',
		'App\Aplicacao\Compartilhado'
	]);

arch('Infraestrutura não pode haver nenhuma entidade de dominio e nem da aplicacao')
	->expect('App\Infraestrutura')
	->not->toUse([
		'App\Dominio',
		'App\Configuracao',
		'App\Aplicacao'
	])
	->ignoring([
		'App\Dominio\Repositorios', // Interfaces
		'App\Aplicacao\Compartilhado', 
		
        'App\Aplicacao\Comandos',
        'App\Aplicacao\Leituras',
        
        'App\Infraestrutura\APIs\Api\Controladores',

        ImplementacaoEscavadorConsultarInformacoesNaInternet::class,

        Authorization::class // Middleware - aqui identifica o Token (empresa & usuarioLogado)
	]);