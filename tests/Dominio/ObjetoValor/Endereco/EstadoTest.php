<?php

use App\Dominio\ObjetoValor\Endereco\Estado;

test('Deve ser uma instância de Estado', function(){
	$estado = new Estado('MG');
	expect($estado)->toBeInstanceOf(Estado::class);
})
	->group('Estado');

test('Deve ser uma erro estado vazio', function(){
	$estado = new Estado('');
})
	->throws('Estado precisa ser informado')
	->group('Estado');

test('Deve ser um estado inválido e numérico', function(){
	$estado = new Estado('42');
})
	->throws('Estado informado não é válido. (42)')
	->group('Estado');

test('Deve ser um estado válido', function(){
	$estado = new Estado('Rio Grande do Sul');
	expect($estado->get())->toEqual('RS');
})
	->group('Estado');

test('Deve ser um estado inexistente numérico', function(){
	$estado = new Estado('421');
})
	->throws('Estado informado não existe. (421)')
	->group('Estado');

test('Deve ser um estado inváldio', function(){
	$estado = new Estado('2');
})
	->throws('Estado informado não é válido. (2)')
	->group('Estado');

test('Deve ser um inexistente', function(){
	$estado = new Estado('WW');
})
	->throws('Estado informado não existe. (WW)')
	->group('Estado');

test('Deve ser um estado válido RS', function(){
	$estado = new Estado('RS');
	expect($estado->get())->toEqual('RS');
})
	->group('Estado');

test('Deve ser um estado inválido', function(){
	$estado = new Estado('RSS');
})
	->throws('Estado informado não existe. (RSS)')
	->group('Estado');

test('Deve ser RS', function(){
	$estado = new Estado('RS');
	expect($estado->get())->toEqual('RS')
		->and($estado->getUF())->toEqual('RS');
})
	->group('Estado');

test('Deve ser Rio Grande do Sul', function(){
	$estado = new Estado('RS');
	expect($estado->getFull())->toEqual('Rio Grande do Sul');
})
	->group('Estado');