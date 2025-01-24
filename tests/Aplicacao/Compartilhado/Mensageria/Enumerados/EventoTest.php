<?php

use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Fila;

test('Deverá ser uma instância de Evento', function(){

	$evento = Evento::EnviarEmail;

	expect($evento)->toBeInstanceOf(Evento::class);
})
	->group('Evento');

test('Deverá retornar o nome do evento', function(){

	$evento = Evento::EnviarEmail;

	expect($evento->name)->toBe('EnviarEmail');
})
	->group('Evento');

test('Deverá retornar o valor do evento', function(){

	$evento = Evento::EnviarEmail;

	expect($evento->value)->toBe('Enviar e-mail');
})
	->group('Evento');

test('Deverá retornar todos as Filas', function(){

	$fila = Evento::EnviarEmail->Filas();

	expect($fila)->toBeInstanceOf(Fila::class)
		->and($fila->name)->toBe('EMISSAO_EMAIL_QUEUE')
		->and($fila->value)->toBe('emissao_email_queue')
		->and($fila)->toBeInstanceOf(Fila::EMISSAO_EMAIL_QUEUE::class);

})
	->group('Evento');