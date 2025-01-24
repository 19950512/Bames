<?php

use App\Dominio\Entidades\Tribunal\EntidadeTribunal;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;

test('Pode instanciar EntidadeTribunal com informações válidas.', function(){

	$entidade = new EntidadeTribunal(
		codigo: new IdentificacaoUnica('ccacc179-05e4-4f3a-8b81-69096370b8ca'),
		codigoTribunal: new TextoSimples('ABC123'),
		nome: new TextoSimples('Tribunal Teste'),
		sigla: new TextoSimples('RS'),
	);
	expect($entidade)->toBeInstanceOf(EntidadeTribunal::class)
	    ->and($entidade->codigo->get())->toBe('ccacc179-05e4-4f3a-8b81-69096370b8ca')
	    ->and($entidade->nome->get())->toBe('Tribunal Teste')
	    ->and($entidade->sigla->get())->toBe('RS');
})
	->group('EntidadeTribunal');