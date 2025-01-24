<?php

declare(strict_types=1);

use App\Dominio\ObjetoValor\Descricao;

test('Deverá ser uma instância de Descricao', function () {
	$descricao = new Descricao('Descrição');
	expect($descricao)->toBeInstanceOf(Descricao::class);
})
	->group('Descricao');

test('Deverá ser uma Descricao com "Esse texto aqui"', function () {

	$descricao = new Descricao('Esse texto aqui');
	expect($descricao->get())->toEqual('Esse texto aqui');
})
	->group('Descricao');
