<?php

use App\Dominio\ObjetoValor\Arquivos;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\ObjetoValor\Endereco\Estado;
use App\Dominio\ObjetoValor\OAB;
use App\Infraestrutura\Adaptadores\ConsultarInformacoesNaInternet\ImplementacaoEscavadorConsultarInformacoesNaInternet;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Authorization;
use App\Infraestrutura\APIs\Api\Controladores\Modelos\FinanceiroController;
use App\Infraestrutura\Repositorios\ConsultarInformacoesNaInternet\ImplementacaoConsultarInformacoesNaInternet;

arch('ObjetoValor não pode utilizar nenhuma classe de nenhuma camada.')
    ->expect('App\Dominio\ObjetoValor')
    ->toUseNothing()
    ->ignoring([
        OAB::class,
    ]);

arch('ObjetoValor não podem extender nem implementar nada.')
    ->expect('App\Dominio\ObjetoValor')
    ->toExtendNothing()
    ->toImplementNothing()
    ->ignoring([
        CPF::class,
        CNPJ::class,
        DocumentoDeIdentificacao::class,
    ]);

arch('ObjetoValor precisam necessáriamente ser final')
    ->expect('App\Dominio\ObjetoValor')
    ->toBeFinal()
    ->ignoring([
	    DocumentoIdentificacao::class
    ]);

arch('ObjetoValor não podem extender nada')
    ->expect('App\Dominio\ObjetoValor')
    ->toExtendNothing();

arch('ObjetoValor possuem o método get')
    ->expect('App\Dominio\ObjetoValor')
    ->toHaveMethod('get');

arch('ObjetoValor possuem construtor publico')
    ->expect('App\Dominio\ObjetoValor')
    ->toHaveConstructor()
    ->ignoring([
	    DocumentoIdentificacao::class
    ]);

arch('ObjetoValor podem ser utilizados somente em App\Domain ou App\Aplicacao exceto Authorization (Middleware).')
    ->expect('App\Dominio\ObjetoValor')
    ->toOnlyBeUsedIn([
        'App\Dominio',
        'App\Aplicacao',
        FinanceiroController::class
    ])
    ->ignoring([
        Authorization::class,
        Arquivos::class,
        ImplementacaoEscavadorConsultarInformacoesNaInternet::class
    ]);