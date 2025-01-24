<?php

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarCPF;
use App\Infraestrutura\Adaptadores\ConsultarInformacoesNaInternet\ImplementacaoEscavadorConsultarInformacoesNaInternet;

test('Deverá ser possível consultar informações na internet', function () {
    $ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('API_BRASIL_UTILIZAR')
        ->andReturn(true)
        ->getMock()

        ->shouldReceive('get')
        ->with('APP_DEBUG')
        ->andReturn(true)
        ->getMock()

        ->shouldReceive('get')
        ->with('API_BRASIL_TOKEN')
        ->andReturn('123456')
        ->getMock();

    $implementacaoEscavadorConsultarInformacoesNaInternet = new ImplementacaoEscavadorConsultarInformacoesNaInternet($ambiente);

    $cpf = '53283600015';
    $response = $implementacaoEscavadorConsultarInformacoesNaInternet->consultarCPF($cpf);

    expect($response)->toBeInstanceOf(SaidaFronteiraConsultarCPF::class);

})->group('EscavadorConsultaInformacoesNaInternet', 'ConsultaCPF')
->skip('Descomente o código para testar a consulta de CPF na internet.');



test('Deverá larnçar um erro com CPF inválido', function () {
    $ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('API_BRASIL_UTILIZAR')
        ->andReturn(true)
        ->getMock()

        ->shouldReceive('get')
        ->with('API_BRASIL_TOKEN')
        ->andReturn('123456')
        ->getMock();

    $implementacaoEscavadorConsultarInformacoesNaInternet = new ImplementacaoEscavadorConsultarInformacoesNaInternet($ambiente);

    $cpf = '12345678902';
    $implementacaoEscavadorConsultarInformacoesNaInternet->consultarCPF($cpf);
})
    ->throws('Erro ao consultar CPF na API Brasil:')
    ->group('EscavadorConsultaInformacoesNaInternet', 'ConsultaCPF')
    ->skip('Descomente o código para testar a consulta de CPF na internet.');