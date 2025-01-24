<?php

use App\Dominio\ObjetoValor\Senha;

test('Deverá retornar - A senha precisa ter no mínimo 8 caracteres', function() {
	$senha = new Senha('123456');
})
	->throws('A senha precisa ter no mínimo 8 caracteres.')
	->group('Senha');

test('Deverá retornar - A senha precisa ter no mínimo 1 letra maiúscula, 1 letra minúscula, 1 número e 1 caractere especial', function() {
	$senha = new Senha('123456789012345678901');
})
	->throws('A senha precisa ter no mínimo 1 letra maiúscula, 1 letra minúscula, 1 número e 1 caractere especial.')
	->group('Senha');

test('Deverá retornar - 2024EanosN0v0z!', function() {
	$senha = new Senha('2024EanosN0v0z!');
	expect($senha->get())->toBe('2024EanosN0v0z!');
})
	->group('Senha');