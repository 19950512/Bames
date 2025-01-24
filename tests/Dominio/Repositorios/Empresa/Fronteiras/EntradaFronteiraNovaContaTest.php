<?php

use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaConta;

test('Deve ser uma instância de EntradaFronteiraNovaConta', function(){
	$entradaFronteiraNovaConta = new EntradaFronteiraNovaConta(
		empresaCodigo: 'iqwiofwqh0-123-=10',
        contaCodigo: 'oṕkfqwpoopu12i-qwf',
        nomeCompleto: 'João da Silva',
        email: 'email@teste.com',
        senha: '1231451216',
        documento: '841676700.97',
        tokenValidacaoEmail: 1234,
        oab: 'RS 123456'
	);
	expect($entradaFronteiraNovaConta)->toBeInstanceOf(EntradaFronteiraNovaConta::class);
})
	->group('EntradaFronteiraNovaConta');
