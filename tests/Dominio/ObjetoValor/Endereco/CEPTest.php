<?php

use App\Dominio\ObjetoValor\Endereco\CEP;

test('Deve ser uma instância de CEP', function(){
	$cep = new CEP('30110-050');
	expect($cep)->toBeInstanceOf(CEP::class);
})
	->group('CEP');

test('Deve ser um CEP com "30110-050"', function(){
	$cep = new CEP('30110-050');
	expect($cep->get())->toEqual('30110-050');
})
	->group('CEP');

test('Deve ser um CEP com "30110050"', function(){
	$cep = new CEP('30110050');
	expect($cep->get())->toEqual('30110-050');
})
	->group('CEP');

test('Deve ser um CEP inváldio', function(){
	$cep = new CEP('30110-0500');
})
	->throws('O CEP informado não é válido. (30110-0500)')
	->group('CEP');
