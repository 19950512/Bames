<?php

use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Localizacao;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Longitude;

test('Deve ser uma instância de Localizacao', function(){

	$latitude = new Latitude(-19.9166813);
	$longitude = new Longitude(-43.9344931);

	$localizacao = new Localizacao(
		latitude: $latitude,
		longitude: $longitude
	);
	expect($localizacao)->toBeInstanceOf(Localizacao::class);
})
	->group('Localizacao');

test('Deve ser uma Localizacao com Latitude -19.9166813 e Longitude -43.9344931', function(){

	$latitude = new Latitude(-19.9166813);
	$longitude = new Longitude(-43.9344931);

	$localizacao = new Localizacao(
		latitude: $latitude,
		longitude: $longitude
	);
	expect($localizacao->getLatitude()->get())->toEqual(-19.9166813)
		->and($localizacao->getLongitude()->get())->toEqual(-43.9344931);
})
	->group('Localizacao');

test('Deve ser uma Localizacao com Latitude -19.9166813 e Longitude -43.9344931 de array', function(){

	$latitude = new Latitude(-19.9166813);
	$longitude = new Longitude(-43.9344931);

	$localizacao = new Localizacao(
		latitude: $latitude,
		longitude: $longitude
	);
	expect($localizacao->get())->toEqual([
		'latitude' => -19.9166813,
		'longitude' => -43.9344931
	]);
})
	->group('Localizacao');
