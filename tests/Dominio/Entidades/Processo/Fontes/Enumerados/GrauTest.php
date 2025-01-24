<?php

use App\Dominio\Entidades\Processo\Fontes\Enumerados\Grau;

test('Deve ser uma instância de Grau', function(){
	$grau = Grau::Primeiro;
	expect($grau)->toBeInstanceOf(Grau::class);
})
	->group('Grau');

test('Deve ser o primeiro grau por extenso', function(){
	$grau = Grau::Primeiro;
	expect($grau->porExtenso())->toBe('Primeiro Grau');
})
	->group('Grau');
