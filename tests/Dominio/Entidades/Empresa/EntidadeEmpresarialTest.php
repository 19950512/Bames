<?php


use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraEmpresa;

test('Pode instanciar EntidadeEmpresarial com informações válidas.', function(){
    $params = new SaidaFronteiraEmpresa(
		empresaCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
	    nome: 'Teste Empresa',
		numeroDocumento: '03623589000172',
        responsavelCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        responsavelOAB: 'RS 123456',
        responsavelNomeCompleto: 'Teste Responsável',
        responsavelEmail: 'emal@para.teste',
        acessoNaoAutorizado: false,
        acessoNaoAutorizadoMotivo: ''
    );

    $entidade = EntidadeEmpresarial::instanciarEntidadeEmpresarial($params);

    expect($entidade)->toBeInstanceOf(EntidadeEmpresarial::class)
	    ->and($entidade->codigo->get())->toBe('ccacc179-05e4-4f3a-8b81-69096370b8ca')
	    ->and($entidade->apelido->get())->toBe('Teste Empresa');
})
	->group('EntidadeEmpresarial');

test('Lança Exception quando tenta instanciar uma Empresa com nome inválido', function(){
    $params = new SaidaFronteiraEmpresa(
		empresaCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        nome: '',
        numeroDocumento: '03623589000172',
        responsavelCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        responsavelOAB: 'RS 123456',
        responsavelNomeCompleto: 'Teste Responsável',
        responsavelEmail: 'emal@para.teste',
        acessoNaoAutorizado: false,
        acessoNaoAutorizadoMotivo: ''
    );

    EntidadeEmpresarial::instanciarEntidadeEmpresarial($params);
})
	->throws(' Apelido informado está inválido. ()')
	->group('EntidadeEmpresarial');

test('Lança Exception quando tenta instanciar uma Empresa com código inválido', function(){
	$params = new SaidaFronteiraEmpresa(
		empresaCodigo: '1234567',
        nome: 'Teste Empresa',
        numeroDocumento: '03623589000172',
        responsavelCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        responsavelOAB: 'RS 123456',
        responsavelNomeCompleto: 'Teste Responsável',
        responsavelEmail: 'emal@para.teste',
        acessoNaoAutorizado: false,
        acessoNaoAutorizadoMotivo: ''
	);

	$entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($params);
})
	->throws('O código informado está inválido. (1234567)')
	->group('EntidadeEmpresarial');

test('Devera retornar um array com todas as informacoes da EntidadeEmpresa', function(){
	$params = new SaidaFronteiraEmpresa(
		empresaCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        nome: 'Teste Empresa',
        numeroDocumento: '03623589000172',
        responsavelCodigo: 'ccacc179-05e4-4f3a-8b81-69096370b8ca',
        responsavelOAB: 'RS 123456',
        responsavelNomeCompleto: 'Teste Responsável',
        responsavelEmail: 'email@para.teste',
        acessoNaoAutorizado: false,
        acessoNaoAutorizadoMotivo: ''
	);

	$entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($params);

	$informacoes = $entidadeEmpresarial->toArray();

	expect($informacoes)->toBeArray()
		->and($informacoes['codigo'])->toBe('ccacc179-05e4-4f3a-8b81-69096370b8ca')
		->and($informacoes['apelido'])->toBe('Teste Empresa')
		->and($informacoes['documentoTipo'])->toBe('CNPJ')
		->and($informacoes['documentoNumero'])->toBe('03.623.589/0001-72')
		->and(count($informacoes))->toBe(10)
        ->and($informacoes['responsavel'])->toBeArray()
        ->and($informacoes['responsavel']['codigo'])->toBe('ccacc179-05e4-4f3a-8b81-69096370b8ca')
        ->and($informacoes['responsavel']['nomeCompleto'])->toBe('Teste Responsável')
        ->and($informacoes['responsavel']['email'])->toBe('email@para.teste')
        ->and(count($informacoes['responsavel']))->toBe(3);
})
	->group('EntidadeEmpresarial');