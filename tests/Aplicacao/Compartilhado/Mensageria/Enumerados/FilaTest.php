<?php

use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Fila;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\TrocaMensagens;

test('Deverá ser uma instância de Fila', function(){

	$fila = Fila::EMISSAO_EMAIL_QUEUE;

	expect($fila)->toBeInstanceOf(Fila::class);
})
	->group('Fila');

test('Deverá retornar o nome da fila', function(){

	$fila = Fila::EMISSAO_EMAIL_QUEUE;

	expect($fila->name)->toBe('EMISSAO_EMAIL_QUEUE');
})
	->group('Fila');

test('Deverá retornar o valor da fila', function(){

	$fila = Fila::EMISSAO_EMAIL_QUEUE;

	expect($fila->value)->toBe('emissao_email_queue');
})
	->group('Fila');

test('Deverá retornar todas as filas', function(){

	$filas = Fila::Filas();

	expect($filas)->toBeArray()
		->and($filas)->toHaveCount(8)
		->and($filas[0]['queue'])->toBeInstanceOf(Fila::class)
		->and($filas[0]['dlx'])->toBeInstanceOf(TrocaMensagens::class);

})
	->group('Fila');

test('Deverá retornar as ligações (Binds)', function(){

	$ligacoes = Fila::Ligacoes();
	expect($ligacoes)->toBeArray()
		->and($ligacoes)->toHaveCount(8)
		->and($ligacoes[0]['queue'])->toBeInstanceOf(Fila::class)
		->and($ligacoes[0]['exchange'])->toBeInstanceOf(TrocaMensagens::class);
})
	->group('Fila');
