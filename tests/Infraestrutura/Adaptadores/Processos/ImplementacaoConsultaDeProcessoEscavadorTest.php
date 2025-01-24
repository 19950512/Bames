<?php

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraProcessosPorOAB;
use App\Infraestrutura\Adaptadores\Processos\ImplementacaoConsultaDeProcessoEscavador;

beforeEach(function(){
    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('API_ESCAVADOR_ACCESS_TOKEN')
        ->andReturn('jajaja')
        ->getMock()
        ->shouldReceive('get')
        ->with('APP_DEBUG')
        ->andReturn(true)
        ->getMock();

    $this->implementacaoConsultaDeProcessoEscavador = new ImplementacaoConsultaDeProcessoEscavador(
        ambiente: $this->ambiente
    );
});

test('Deverá ser uma instância de ImplementacaoConsultaDeProcessoEscavador', function(){
    expect($this->implementacaoConsultaDeProcessoEscavador)->toBeInstanceOf(ImplementacaoConsultaDeProcessoEscavador::class);
})
    ->group('ImplementacaoConsultaDeProcessoEscavador')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');

test('Deverá consultar os processos de um CPF (61908533072)', function(){

    $processos = $this->implementacaoConsultaDeProcessoEscavador->numeroDocumento('61908533072');

    expect($processos)->toBeInstanceOf(SaidaFronteiraProcessosPorOAB::class);
})
    ->group('ImplementacaoConsultaDeProcessoEscavador', 'ImplementacaoConsultaDeProcessoEscavadorCPF')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');

test('Deverá criar o monitoramento de um CNJ (5012533-92.2021.4.04.9999)', function(){

    $monitoramento = $this->implementacaoConsultaDeProcessoEscavador->monitorarUmProcesso('5012533-92.2021.4.04.9999');
    expect($monitoramento)->toBeNull();
})
    ->group('ImplementacaoConsultaDeProcessoEscavador')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');

test('Deverá consultar os processos de uma OAB (OAB/RS 133.074)', function(){

    $processos = $this->implementacaoConsultaDeProcessoEscavador->OAB('OAB/RS 133.074');
    expect($processos)->toBeInstanceOf(SaidaFronteiraProcessosPorOAB::class);
})
    ->group('ImplementacaoConsultaDeProcessoEscavador')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');

test('Deverá consultar as movimentações de um processo (CNJ 5012533-92.2021.4.04.9999)', function(){

    $movimentacoes = $this->implementacaoConsultaDeProcessoEscavador->obterMovimentacoesDoProcesso('5012533-92.2021.4.04.9999');
    expect($movimentacoes)->toBeArray();
})
    ->group('ImplementacaoConsultaDeProcessoEscavador')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');

test('Deverá solicitar a atualização do processo (CNJ 5012533-92.2021.4.04.9999)', function(){
    expect($this->implementacaoConsultaDeProcessoEscavador->solicitarAtualizacaoDoProcesso('5012533-92.2021.4.04.9999'))->toBeNull();
})
    ->group('ImplementacaoConsultaDeProcessoEscavador')
    ->skip('Isso foi apenas um teste de exemplo, não executar mais');