<?php

use App\Aplicacao\Compartilhado\Mensageria\Enumerados\TrocaMensagens;

test('Deverá ser uma instância de TrocaMensagens', function(){

	$trocaMensagens = TrocaMensagens::EMISSAO_BOLETO_EXCHANGE;

	expect($trocaMensagens)->toBeInstanceOf(TrocaMensagens::class);
})
	->group('TrocaMensagens');

test('Deverá retornar o nome da troca de mensagens', function(){

	$trocaMensagens = TrocaMensagens::EMISSAO_BOLETO_EXCHANGE;

	expect($trocaMensagens->name)->toBe('EMISSAO_BOLETO_EXCHANGE');
})
	->group('TrocaMensagens');

test('Deverá retornar o valor da troca de mensagens', function(){

	$trocaMensagens = TrocaMensagens::EMISSAO_BOLETO_EXCHANGE;

	expect($trocaMensagens->value)->toBe('emissao_boleto_exchange');
})
	->group('TrocaMensagens');

test('Deverá retornar todas as trocas de mensagens', function(){

	$trocasMensagens = TrocaMensagens::trocasMensagens();

	expect($trocasMensagens)->toBeArray()
		->and($trocasMensagens)->toHaveCount(12)
		->and($trocasMensagens[0]['exchange'])->toBeInstanceOf(TrocaMensagens::class)
		->and($trocasMensagens[0]['type'])->toBeString();
})
	->group('TrocaMensagens');
