<?php


use App\Dominio\ObjetoValor\NomeCompleto;

test('Deve ser uma instância de NomeCompleto', function(){
    $nomeCompleto = new NomeCompleto('Matheus Maydana');
    expect($nomeCompleto)->toBeInstanceOf(NomeCompleto::class);
})
	->group('NomeCompleto');

test('Rita de Cássia da Silva Rosa, deve ser uma nome válido', function(){
    $nomeCompleto = new NomeCompleto('Rita de Cássia da Silva Rosa');
    expect(($nomeCompleto)->get())->toEqual('Rita de Cássia da Silva Rosa');
})
	->group('NomeCompleto');

test('Deve ser camel case', function(){
    $nomeCompleto = new NomeCompleto('RITA DE CÁSSIA DA SILVA ROSA');
    expect(($nomeCompleto)->get())->toEqual('Rita de Cássia da Silva Rosa');
})
	->group('NomeCompleto');

test('Deve ser um nome inválido.', function(){
    $nomeCompleto = new NomeCompleto('Rita de Cássia da Silva Rosa 123123');
})
	->throws('Nome completo informado está inválido. (Rita de Cássia da Silva Rosa 123123)')
	->group('NomeCompleto');