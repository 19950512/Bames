<?php

// Valor::class
use App\Dominio\ObjetoValor\Valor;

test('Deve ser uma instância de Valor', function(){
	$valor = new Valor(10.00);
	expect($valor)->toBeInstanceOf(Valor::class);
})->group('Valor');

test('Valor deve ser 10.00', function(){
	$valor = new Valor(10.00);
	expect($valor->get())->toBe(10.00);
})->group('Valor');