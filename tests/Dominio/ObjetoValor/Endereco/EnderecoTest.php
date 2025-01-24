<?php

use App\Dominio\ObjetoValor\Endereco\CEP;
use App\Dominio\ObjetoValor\Endereco\Endereco;
use App\Dominio\ObjetoValor\Endereco\Estado;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Localizacao;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Longitude;
use App\Dominio\ObjetoValor\Endereco\Pais;
use App\Dominio\ObjetoValor\TextoSimples;

test('Deve ser uma instância de Endereco', function(){
	$endereco = new Endereco(
		rua: new TextoSimples('Rua Olimpio Tramontina'),
		numero: new TextoSimples('176'),
		bairro: new TextoSimples('Portal do Sol'),
		cidade: new TextoSimples('Marau'),
		estado: new Estado('RS'),
		pais: new Pais('Brazil'),
		cep: new CEP('99150-000'),
		complemento: new TextoSimples('AP 302'),
		referencia: new TextoSimples('Próximo ao mercado'),
		localizacao: new Localizacao(
			latitude: new Latitude(-28.432072),
			longitude: new Longitude(-52.198615)
		)
	);
	expect($endereco)->toBeInstanceOf(Endereco::class);
})
	->group('Endereco');


test('Deve ser uma lista com os dados do endereço', function(){
	$endereco = new Endereco(
		rua: new TextoSimples('Rua Olimpio Tramontina'),
		numero: new TextoSimples('176'),
		bairro: new TextoSimples('Portal do Sol'),
		cidade: new TextoSimples('Marau'),
		estado: new Estado('RS'),
		pais: new Pais('Brazil'),
		cep: new CEP('99150-000'),
		complemento: new TextoSimples('AP 302'),
		referencia: new TextoSimples('Próximo ao mercado'),
		localizacao: new Localizacao(
			latitude: new Latitude(-28.432072),
			longitude: new Longitude(-52.198615)
		)
	);

	expect($endereco->get())->toEqual([
		'rua' => 'Rua Olimpio Tramontina',
		'numero' => '176',
		'bairro' => 'Portal do Sol',
		'cidade' => 'Marau',
		'estado' => 'RS',
		'pais' => 'BR',
		'cep' => '99150-000',
		'complemento' => 'AP 302',
		'referencia' => 'Próximo ao mercado',
		'localizacao' => [
			'latitude' => -28.432072,
			'longitude' => -52.198615
		]
	]);
})
	->group('Endereco');

test('Deve ser o endereco completo em 1 frase.', function(){

	$endereco = new Endereco(
		rua: new TextoSimples('Rua Olimpio Tramontina'),
		numero: new TextoSimples('176'),
		bairro: new TextoSimples('Portal do Sol'),
		cidade: new TextoSimples('Marau'),
		estado: new Estado('RS'),
		pais: new Pais('Brazil'),
		cep: new CEP('99150-000'),
		complemento: new TextoSimples('AP 302'),
		referencia: new TextoSimples('Próximo ao mercado'),
		localizacao: new Localizacao(
			latitude: new Latitude(-28.432072),
			longitude: new Longitude(-52.198615)
		)
	);

	expect($endereco->enderecoCompleto())->toEqual('Rua Olimpio Tramontina, 176, Portal do Sol, Marau, Rio Grande do Sul, Brazil, 99150-000');
})
	->group('Endereco');

test('Devera criar um endereco setando a rua', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'rua' => 'Rua Olimpio Tramontina'
	]);

	expect($endereco->rua)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->rua->get())->toEqual('Rua Olimpio Tramontina');
})
	->group('Endereco');

test('Devera criar um endereco setando o numero', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'numero' => '176'
	]);

	expect($endereco->numero)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->numero->get())->toEqual('176');
})
	->group('Endereco');

test('Devera criar um endereco setando o bairro', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'bairro' => 'Portal do Sol'
	]);

	expect($endereco->bairro)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->bairro->get())->toEqual('Portal do Sol');
})
	->group('Endereco');

test('Devera criar um endereco setando a cidade', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'cidade' => 'Marau'
	]);

	expect($endereco->cidade)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->cidade->get())->toEqual('Marau');
})
	->group('Endereco');

test('Devera criar um endereco setando o estado', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'estado' => 'RS'
	]);

	expect($endereco->estado)->toBeInstanceOf(Estado::class)
		->and($endereco->estado->get())->toEqual('RS');
})
	->group('Endereco');

test('Devera criar um endereco setando o pais', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'pais' => 'Brazil'
	]);

	expect($endereco->pais)->toBeInstanceOf(Pais::class)
		->and($endereco->pais->get())->toEqual('BR');
})
	->group('Endereco');

test('Devera criar um endereco setando o cep', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'cep' => '99150-000'
	]);

	expect($endereco->cep)->toBeInstanceOf(CEP::class)
		->and($endereco->cep->get())->toEqual('99150-000');
})
	->group('Endereco');

test('Devera criar um endereco setando o complemento', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'complemento' => 'AP 302'
	]);

	expect($endereco->complemento)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->complemento->get())->toEqual('AP 302');
})
	->group('Endereco');

test('Devera criar um endereco setando a referencia', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'referencia' => 'Próximo ao mercado'
	]);

	expect($endereco->referencia)->toBeInstanceOf(TextoSimples::class)
		->and($endereco->referencia->get())->toEqual('Próximo ao mercado');
})
	->group('Endereco');

test('Devera criar um endereco setando a localizacao', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'latitude' => -28.432072,
		'longitude' => -52.198615
	]);

	expect($endereco->localizacao)->toBeInstanceOf(Localizacao::class)
		->and($endereco->localizacao->get())->toEqual([
			'latitude' => -28.432072,
			'longitude' => -52.198615
		]);
})
	->group('Endereco');

test('Devera criar um endereco setando todos os parametros', function() {

	$endereco = new Endereco();
	$endereco->setParams([
		'rua' => 'Rua Olimpio Tramontina',
		'numero' => '176',
		'bairro' => 'Portal do Sol',
		'cidade' => 'Marau',
		'estado' => 'RS',
		'pais' => 'Brazil',
		'cep' => '99150-000',
		'complemento' => 'AP 302',
		'referencia' => 'Próximo ao mercado',
		'latitude' => -28.432072,
		'longitude' => -52.198615
	]);

	expect($endereco->get())->toEqual([
		'rua' => 'Rua Olimpio Tramontina',
		'numero' => '176',
		'bairro' => 'Portal do Sol',
		'cidade' => 'Marau',
		'estado' => 'RS',
		'pais' => 'BR',
		'cep' => '99150-000',
		'complemento' => 'AP 302',
		'referencia' => 'Próximo ao mercado',
		'localizacao' => [
			'latitude' => -28.432072,
			'longitude' => -52.198615
		]
	]);
})
	->group('Endereco');