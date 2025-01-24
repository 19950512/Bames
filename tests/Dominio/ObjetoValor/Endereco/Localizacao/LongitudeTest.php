<?php

use App\Dominio\ObjetoValor\Endereco\Localizacao\Longitude;

test('Deve ser uma instância de Longitude', function(){
	$longitude = new Longitude(-43.9344931);
	expect($longitude)->toBeInstanceOf(Longitude::class);
})
	->group('Longitude');

test('Deve ser uma Longitude com -43.9344931', function(){
	$longitude = new Longitude(-43.9344931);
	expect($longitude->get())->toEqual(-43.9344931);
})
	->group('Longitude');

test('Deve ser uma longitude inválida negativa', function(){

	$longitude = new Longitude(-181);
})
	->throws('Longitude informada não é válida. (-181)')
	->group('Longitude');

test('Deve ser uma longitude inválida positiva', function(){

	$longitude = new Longitude(181);
})
	->throws('Longitude informada não é válida. (181)')
	->group('Longitude');