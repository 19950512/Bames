<?php

use App\Dominio\ObjetoValor\Ativo;

test('Deve ser uma instância de Ativo', function(){
	$ativo = new Ativo(true);
	expect($ativo)->toBeInstanceOf(Ativo::class);
})->group('Ativo');

test('Ativo deve ser true', function(){
	$ativo = new Ativo(true);
	expect($ativo->get())->toBeTrue();
})->group('Ativo');

test('Ativo deve ser false', function(){
	$ativo = new Ativo(false);
	expect($ativo->get())->toBeFalse();
})->group('Ativo');