<?php

use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

test('Deve ser uma instância de SaidaFronteiraBuscarContaPorCodigo', function(){
	$saidaFronteiraBuscarContaPorCodigo = new SaidaFronteiraBuscarContaPorCodigo(
		empresaCodigo: '0213801293709812730128',
        contaCodigo: '0213801293709812730128',
        nomeCompleto: 'João da Silva',
        email: 'email@email.com',
        documento: '84167670097',
        hashSenha: '$argon2i$v=19$m=65536,t=4,p=1$V0pJdm1qTElDNUoyMnVhYQ$456NYptMUfHf1pnB3kwva2u4YkFmlgaGbvaa0sGuU84',
        oab: 'RS 123456'
	);
	expect($saidaFronteiraBuscarContaPorCodigo)->toBeInstanceOf(SaidaFronteiraBuscarContaPorCodigo::class);
})
	->group('SaidaFronteiraBuscarContaPorCodigo');