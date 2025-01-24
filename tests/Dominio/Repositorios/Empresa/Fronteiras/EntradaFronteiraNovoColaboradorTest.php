<?php

use App\Dominio\Repositorios\Empresa\Fronteiras\EntradaFronteiraNovoColaborador;

test('Deve ser uma instância de EntradaFronteiraNovoColaborador', function(){
	$entradaFronteiraNovoColaborador = new EntradaFronteiraNovoColaborador(
		empresaCodigo: '645123213',
		colaboradorCodigo: '123',
		nomeCompleto: 'João da Silva',
		email: 'email@teste.com',
        oab: 'RS 123456'
	);
	expect($entradaFronteiraNovoColaborador)->toBeInstanceOf(EntradaFronteiraNovoColaborador::class);
})
	->group('EntradaFronteiraNovoColaborador');
