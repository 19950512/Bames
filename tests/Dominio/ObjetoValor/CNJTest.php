<?php

use App\Dominio\ObjetoValor\CNJ;

test('Deve ser uma instância de CNJ', function(){
	$cnj = new CNJ('0053087-35.2013.8.13.0693');
	expect($cnj)->toBeInstanceOf(CNJ::class);
})
	->group('CNJ');

test('Deve ser um CNJ válido.', function(){
	expect((new CNJ('0053087-35.2013.8.13.0693'))->get())->toEqual('0053087-35.2013.8.13.0693')
		->and((new CNJ('00530873520138130693'))->get())->toEqual('0053087-35.2013.8.13.0693')
		->and((new CNJ('0053087-35.2013.8.13.0693'))->get())->toEqual('0053087-35.2013.8.13.0693')
		->and((new CNJ('00530873520138130693'))->get())->toEqual('0053087-35.2013.8.13.0693');
})
	->group('CNJ');

test('Deve ser um CNJ inválido.', function(){
	new CNJ('0053087-35.2013.8.13.06');
})
	->throws('O número Conselho Nacional de Justiça (CNJ) está inválido. - 0053087-35.2013.8.13.06')
	->group('CNJ');

test('Deve ser um CNJ inválido pra mais.', function(){
	new CNJ('0053087-35.2013.8.13.067484');
})
	->throws('O número Conselho Nacional de Justiça (CNJ) está inválido. - 0053087-35.2013.8.13.067484')
	->group('CNJ');

