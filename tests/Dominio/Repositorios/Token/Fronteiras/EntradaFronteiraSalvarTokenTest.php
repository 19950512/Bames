<?php

use App\Dominio\Repositorios\Token\Fronteiras\EntradaFronteiraSalvarToken;

test('Deve ser uma instância de EntradaFronteiraSalvarToken', function(){
	$entradaFronteiraSalvarToken = new EntradaFronteiraSalvarToken(
		token: 'pioqjwiohpwqipoqwoipy43op11',
		codigoContaBancaria: 'poqjnwefpiohqwfpojqwipowyi123',
		bancoNome: 'Banco do Brasil',
		tempoExpiracao: '3600',
		expirarEm: '2021-10-10 10:10:10',
	);
	expect($entradaFronteiraSalvarToken)->toBeInstanceOf(EntradaFronteiraSalvarToken::class);
})
	->group('EntradaFronteiraSalvarToken');
