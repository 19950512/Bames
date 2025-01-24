<?php

use App\Dominio\Repositorios\Token\Fronteiras\SaidaFronteiraToken;

test('Deve ser uma instância de SaidaFronteiraToken', function(){
	$saidaFronteiraToken = new SaidaFronteiraToken(
		codigo: '0213801293709812730128',
		token: 'qwfoiqwofih',
		expiraEm: '3600',
		tokenMomentoCriacao: '2021-10-10 10:10:10',
	);
	expect($saidaFronteiraToken)->toBeInstanceOf(SaidaFronteiraToken::class);
})
	->group('SaidaFronteiraToken');