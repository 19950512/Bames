<?php

// Apelido::class

use App\Dominio\ObjetoValor\Apelido;

test('Deve ser uma instancia de Apelido', function () {

	$apelido = new Apelido('Apelido');

	expect($apelido)->toBeInstanceOf(Apelido::class);
})
	->group('Apelido');

test('Deve ser um Apelido com "Apelido"', function () {

	$apelido = new Apelido('Apelido');

	expect($apelido->get())->toEqual('Apelido');
})
	->group('Apelido');

test('Deve ser um Apelido com "Maydana"', function () {

	$apelido = new Apelido('Maydana');

	expect($apelido->get())->toEqual('Maydana');
})
	->group('Apelido');

test('Deve ser um Apelido com "Matheus Maydana"', function () {

	$apelido = new Apelido('Matheus Maydana');

	expect($apelido->get())->toEqual('Matheus Maydana');
})
	->group('Apelido');


test('Deve ser um Apelido com "rita de cássia da silva rosa" deverá ser "Rita de Cássia da Silva Rosa"', function () {

	$apelido = new Apelido('rita de cássia da silva rosa');

	expect($apelido->get())->toEqual('Rita de Cássia da Silva Rosa');
})
	->group('Apelido');

test('Deve ser um Apelido com "Advogado 157" inválido, pois há números', function () {

	$apelido = new Apelido('Advogado 157');

	expect($apelido->get())->toEqual('Advogado 157');
})
    ->throws('Apelido informado está inválido. (Advogado 157)')
	->group('Apelido');

test('Deve ser um Apelido com "Um tiro de justiça"', function () {

	$apelido = new Apelido('Um tiro de justiça');

	expect($apelido->get())->toEqual('Um Tiro de Justiça');
})
	->group('Apelido');

test('Deve ser um Apelido com "Maydana, o Brabo" inválido, pois há virgula', function () {

	$apelido = new Apelido('Maydana, o Brabo');

	expect($apelido->get())->toEqual('Maydana, o Brabo');
})
    ->throws('Apelido informado está inválido. (Maydana, O Brabo)')
	->group('Apelido');

test('Deve ser um Apelido com "Apelido Legal"', function () {

	$apelido = new Apelido('apelido legal');

	expect($apelido->get())->toEqual('Apelido Legal');
})
	->group('Apelido');

test('Deve retornar um erro, apelido inválido', function () {

	$apelido = new Apelido('!!apelido legal!!');
})
	->throws('Apelido informado está inválido. (!!apelido Legal!!)')
	->group('Apelido');