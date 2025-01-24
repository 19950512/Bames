<?php

use App\Dominio\ObjetoValor\Endereco\Pais;

test('Deve ser uma instância de Pais', function(){
	$pais = new Pais('Brazil');
	expect($pais)->toBeInstanceOf(Pais::class);
})
	->group('Pais');

test('Deve informar um Pais', function(){
	$pais = new Pais('');
	expect($pais)->toBeInstanceOf(Pais::class);
})
	->throws('País não informado.')
	->group('Pais');

test('Deve ser um pais inexistente', function(){
	$pais = new Pais('BRR');
	expect($pais)->toBeInstanceOf(Pais::class);
})
	->throws('País informado não existe. (BRR)')
	->group('Pais');

test('Deve ser um pais inválido', function(){
	$pais = new Pais('B');
	expect($pais)->toBeInstanceOf(Pais::class);
})
	->throws('País informado não é válido. (B)')
	->group('Pais');

test('Deve informar erro de país inexistente', function(){
	$pais = new Pais('WW');
	expect($pais)->toBeInstanceOf(Pais::class);
})
	->throws('País informado não existe. (WW)')
	->group('Pais');

test('A sigla do pais deve ser BR', function(){
	$pais = new Pais('Brazil');
	expect($pais->getUF())->toEqual('BR')
		->and($pais->get())->toEqual('BR');
})
	->group('Pais');

test('O país deve ser inválido', function(){
	$pais = new Pais('22');
})
	->throws('País informado não é válido. (22)')
	->group('Pais');

test('O país deve ser Brazil', function(){
	$pais = new Pais('BR');
	expect($pais->getFull())->toEqual('Brazil');
})
	->group('Pais');
