<?php

use App\Dominio\ObjetoValor\IdentificacaoUnica;

test('Deve ser uma instância de IdentificacaoUnica', function(){
	$identificacaoUnica = new IdentificacaoUnica();
	expect($identificacaoUnica)->toBeInstanceOf(IdentificacaoUnica::class);
})
	->group('IdentificacaoUnica');

test('Deve ser um identificador único', function(){
	$identificacaoUnica = new IdentificacaoUnica();
	expect($identificacaoUnica->get())->toBeString();
})
	->group('IdentificacaoUnica');

test('Deve ser um identificador único f4dbb40e-721d-4b6a-9941-f79719e7b2e6', function(){
	$identificacaoUnica = new IdentificacaoUnica('f4dbb40e-721d-4b6a-9941-f79719e7b2e6');
	expect($identificacaoUnica->get())->toEqual('f4dbb40e-721d-4b6a-9941-f79719e7b2e6');
})
	->group('IdentificacaoUnica');

test('Deve ser um identificador único 1234567', function(){
	$identificacaoUnica = new IdentificacaoUnica('1234567');
})
	->throws('O código informado está inválido. (1234567)')
	->group('IdentificacaoUnica');