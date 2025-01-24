<?php

use App\Aplicacao\Compartilhado\HTTP\RespostaHTTP;

test('Deverá ser uma instância de RespostaHTTP', function(){

	$resposta = new RespostaHTTP(
		code: 200,
		body: [
			'Mensagem OK'
		]
	);

	expect($resposta)->toBeInstanceOf(RespostaHTTP::class);
})
	->group('RespostaHTTP');

test('Code de status HTTP inválido com codigo 600', function(){

	$resposta = new RespostaHTTP(
		code: 600,
		body: [
			'Mensagem OK'
		]
	);
})
	->throws('Código de status HTTP inválido. (600)')
	->group('RespostaHTTP');

test('Code de status HTTP inválido com codigo 99', function(){

	$resposta = new RespostaHTTP(
		code: 99,
		body: [
			'Mensagem OK'
		]
	);
})
	->throws('Código de status HTTP inválido. (99)')
	->group('RespostaHTTP');


test('Code de status HTTP inválido', function(){

	$resposta = new RespostaHTTP(
		code: 0,
		body: [
			'Mensagem OK'
		]
	);
})
	->throws('Código de status HTTP inválido. (0)')
	->group('RespostaHTTP');
