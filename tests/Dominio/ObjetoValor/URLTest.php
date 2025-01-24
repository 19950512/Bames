<?php

use App\Dominio\ObjetoValor\URL;

test('Deve ser uma instância de URL', function(){
	$url = new URL('http://www.teste.com');
	expect($url)->toBeInstanceOf(URL::class);
})
	->group('URL');

test('O valor deve ser http://www.teste.com', function(){
	$url = new URL('http://www.teste.com');
	expect($url->get())->toBe('http://www.teste.com');
})
	->group('URL');

test('O dominio deve ser teste.com', function(){
	$url = new URL('http://www.teste.com');
	expect($url->dominio)->toBe('teste.com');
})
	->group('URL');

test('A URL é inválida', function(){
	$url = new URL('www.teste.com');
})
	->throws('URL informada está inválida. www.teste.com')
	->group('URL');

test('O subdominio deve ser central', function(){
	$url = new URL('http://central.teste.com');
	expect($url->subDominio)->toBe('central');
})
	->group('URL');

test('O caminho deve ser /', function(){
	$url = new URL('http://www.teste.com');
	expect($url->uri)->toBe('/');
})
	->group('URL');

test('O caminho deve ser /teste', function(){
	$url = new URL('http://www.teste.com/teste');
	expect($url->uri)->toBe('/teste');
})
	->group('URL');

test('O protocolo deve ser http', function(){
	$url = new URL('http://www.teste.com');
	expect($url->protocolo)->toBe('http');
})
	->group('URL');

test('O protocolo deve ser https', function(){
	$url = new URL('https://www.teste.com');
	expect($url->protocolo)->toBe('https');
})
	->group('URL');