<?php

use App\Aplicacao\Compartilhado\Containers\Container;

test('Deve ser uma instância de Container', function(){

	$container = Container::getInstance();

	expect($container)->toBeInstanceOf(Container::class);
})
	->group('Container');

test("Deve retornar AMOR do get('Rita')", function(){

	$container = Container::getInstance();
	$container = $container->get([]);

	$container->set('Rita', 'AMOR');

	expect($container->get('Rita'))->toBe('AMOR');
})
	->group('Container');


test("O Container get só pode setar as dependencias 1x", function(){

	$container_A = Container::getInstance();

	// Inicia o container com a dependencia DB_PORT com valor de 42
	$container_A = $container_A->get([
		'DB_PORT' => 42
	]);

	expect($container_A->get('DB_PORT'))->toBe(42);

	$container_B = Container::getInstance();

	// Inicia o "outro" container com a dependencia DB_PORT com valor de 77 porem, como o container já foi instanciado, o valor não será alterado
	$container_B = $container_B->get([
		'DB_PORT' => 77
	]);

	expect($container_B->get('DB_PORT'))->toBe(42);
})
	->group('Container')
	->skip('Não é possível testar o comportamento do singleton, pois o container é instanciado em outros testes e não é possível resetar o container');

test('O Container não pode ser instanciado', function(){

	$container = new Container();
})
	->throws('Call to private')
	->group('Container');

