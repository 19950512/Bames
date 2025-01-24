<?php

use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;

test('Deve ser uma instância de Latitude', function(){
	$latitude = new Latitude(-19.9166813);
	expect($latitude)->toBeInstanceOf(Latitude::class);
})
	->group('Latitude');


test('Deve ser uma Latitude com -19.9166813', function(){
	$latitude = new Latitude(-19.9166813);
	expect($latitude->get())->toEqual(-19.9166813);
})
	->group('Latitude');

test('Deve ser uma latitude inválida negativa', function(){
	$latitude = new Latitude(-91);
})
	->throws('Latitude informada não é válida. (-91)')
	->group('Latitude');

test('Deve ser uma latitude inválida positiva', function(){
	$latitude = new Latitude(91);
})
	->throws('Latitude informada não é válida. (91)')
	->group('Latitude');

test('Deve ser uma latitude inválida', function(){
	$latitude = new Latitude(91.1);
})
	->throws('Latitude informada não é válida. (91.1)')
	->group('Latitude');

