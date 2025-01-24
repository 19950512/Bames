<?php

use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaEmpresa;

test('Deve ser uma instância de EntradaFronteiraNovaEmpresa', function(){
	$entradaFronteiraNovaEmpresa = new EntradaFronteiraNovaEmpresa(
		empresaCodigo: 'iqwiofwqh0-123-=10',
		apelido: 'Empresa Teste LTDA',
        numeroDocumento: '12345678901234',
        responsavelEmail: 'mattmaydana@gmail.com',
	);
	expect($entradaFronteiraNovaEmpresa)->toBeInstanceOf(EntradaFronteiraNovaEmpresa::class);
})
	->group('EntradaFronteiraNovaEmpresa');