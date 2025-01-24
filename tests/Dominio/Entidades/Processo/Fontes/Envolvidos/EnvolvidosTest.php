<?php

use App\Dominio\Entidades\Processo\Fontes\Envolvidos\EntidadeEnvolvido;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Natureza;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Polo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Tipo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Envolvidos;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;

test('Deve ser uma instância de Envolvidos', function(){
	$envolvidos = new Envolvidos();
	expect($envolvidos)->toBeInstanceOf(Envolvidos::class);
})
	->group('Envolvidos');

test('Deve ser um array de envolvidos', function(){

	$envolvidos = new Envolvidos();

	$envolvidos->add(
		new EntidadeEnvolvido(
			codigo: new IdentificacaoUnica(),
			nomeCompleto: new NomeCompleto('Nome Teste'),
			quantidadeProcessos: 2,
			tipoNatureza: Natureza::Fisica,
			documento: new CPF('1234567890'),
			tipo: Tipo::Apelado,
			polo: Polo::Ativo
		)
	);

	expect($envolvidos->get())->toBeArray();
})
	->group('Envolvidos');