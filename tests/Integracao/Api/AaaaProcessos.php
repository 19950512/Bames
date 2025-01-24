<?php

use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;

global $jwt;

if(!is_file(__DIR__.'/../../../.env')) {
    return;
}

$CNJdoProcesso = '';

beforeEach(function(){
    
	$this->clientHTTPAuth = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8052'
	]);
    
	$this->clientHTTPApi = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8053'
	]);
});

describe('(Processos):', function() use (&$jwt, &$CNJdoProcesso){

    it("Deverá retornar uma lista de processos da empresa com 20 processos.", function() use (&$jwt, &$CNJdoProcesso){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/processos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('numeroCNJ')
            ->and($resposta->body[0])->toHaveKey('dataUltimaMovimentacao')
            ->and($resposta->body[0])->toHaveKey('quantidadeMovimentacoes')
            ->and($resposta->body[0])->toHaveKey('demandante')
            ->and($resposta->body[0])->toHaveKey('demandado')
            ->and($resposta->body[0])->toHaveKey('ultimaMovimentacaoData')
            ->and($resposta->body[0])->toHaveKey('ultimaMovimentacaoDescricao')
            ->and($resposta->body)->toHaveCount(20);

        $CNJdoProcesso = $resposta->body[0]['numeroCNJ'];

    })->group('Integracao', 'Processos');

    it('Conseguimos identificar um processo e vamos usar o CNJ para consultar depois', function() use (&$jwt, &$CNJdoProcesso){
        expect($CNJdoProcesso)->not->toBeEmpty()
            ->and($CNJdoProcesso)->toEqual($CNJdoProcesso);
    });

    it('Vamos solicitar todas as movimentações do processo CNJ', function() use (&$jwt, &$CNJdoProcesso){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->post('/processos/consultarMovimentacoes', [
            'cnj' => $CNJdoProcesso
        ]);

        expect($resposta->code)->toEqual(200)
            ->and($resposta->body['message'])->toEqual('Movimentações consultadas com sucesso');
    })->group('Integracao', 'Processos');

    it('Vamos consultar novamente os processos para ver as movimentacoes', function() use (&$jwt, &$CNJdoProcesso){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/processos');

        $processoFiltrado = array_filter($resposta->body, function($processo) use ($CNJdoProcesso){
            return $processo['numeroCNJ'] == $CNJdoProcesso;
        });

        expect($processoFiltrado)->not->toBeEmpty()
            ->and($processoFiltrado)->toHaveCount(1);

        $processoFiltrado = $processoFiltrado[key($processoFiltrado)];

        expect($resposta->code)->toBe(200)
            ->and($processoFiltrado)->toHaveKey('codigo')
            ->and($processoFiltrado)->toHaveKey('numeroCNJ')
            ->and($processoFiltrado)->toHaveKey('dataUltimaMovimentacao')
            ->and($processoFiltrado)->toHaveKey('quantidadeMovimentacoes')
            ->and($processoFiltrado)->toHaveKey('demandante')
            ->and($processoFiltrado)->toHaveKey('demandado')
            ->and($processoFiltrado)->toHaveKey('ultimaMovimentacaoData')
            ->and($processoFiltrado)->toHaveKey('ultimaMovimentacaoDescricao');

        $CNJdoProcesso = $resposta->body[0]['numeroCNJ'];

    })->group('Integracao', 'Processos');

    it('Todas as informações necessárias da movimentação devem ser: id, empresaCodigo, processoCodigo, etc.', function() use (&$jwt, &$CNJdoProcesso){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/processos');

        $processoFiltrado = array_filter($resposta->body, function($processo) use ($CNJdoProcesso){
            return $processo['numeroCNJ'] == $CNJdoProcesso;
        });

        $processoFiltrado = $processoFiltrado[key($processoFiltrado)];

        $movimentacoes = $processoFiltrado['movimentacoes'];

        foreach($movimentacoes as $movimentacao){
            expect($movimentacao)->toHaveKey('id')
                ->and($movimentacao)->toHaveKey('empresaCodigo')
                ->and($movimentacao)->toHaveKey('processoCodigo')
                ->and($movimentacao)->toHaveKey('processoCNJ')
                ->and($movimentacao)->toHaveKey('data')
                ->and($movimentacao)->toHaveKey('tipo')
                ->and($movimentacao)->toHaveKey('tipoPublicacao')
                ->and($movimentacao)->toHaveKey('classificacaoPreditaNome')
                ->and($movimentacao)->toHaveKey('classificacaoPreditaDescricao')
                ->and($movimentacao)->toHaveKey('classificacaoPreditaHierarquia')
                ->and($movimentacao)->toHaveKey('conteudo')
                ->and($movimentacao)->toHaveKey('textoCategoria')
                ->and($movimentacao)->toHaveKey('fonteProcessoFonteId')
                ->and($movimentacao)->toHaveKey('fonteFonteId')
                ->and($movimentacao)->toHaveKey('fonteNome')
                ->and($movimentacao)->toHaveKey('fonteTipo')
                ->and($movimentacao)->toHaveKey('fonteSigla')
                ->and($movimentacao)->toHaveKey('fonteGrau')
                ->and($movimentacao)->toHaveKey('fonteGrauFormatado');
        }

        expect($movimentacoes)->toHaveCount(30);

    })->group('Integracao', 'Processos')
        ->skip('Falta implementar um get detalhes do processo ai vem as movimentações');


    it('Todas as informações dos envolvidos são: codigo, nomeCompleto, documento, oab, tipo, quantidadeDeProcessos.', function() use (&$jwt, &$CNJdoProcesso) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/processos');

        $processoFiltrado = array_filter($resposta->body, function ($processo) use ($CNJdoProcesso) {
            return $processo['numeroCNJ'] == $CNJdoProcesso;
        });

        $processoFiltrado = $processoFiltrado[key($processoFiltrado)];

        $envolvidos = $processoFiltrado['envolvidos'];

        foreach($envolvidos as $envolvido) {

            expect($envolvido)->toHaveKey('codigo')
                ->and($envolvido)->toHaveKey('oab')
                ->and($envolvido)->toHaveKey('tipo')
                ->and($envolvido)->toHaveKey('nomeCompleto')
                ->and($envolvido)->toHaveKey('documento')
                ->and($envolvido)->toHaveKey('quantidadeDeProcessos');
        }

        expect($envolvidos)->toHaveCount(6);

    })->group('Integracao', 'Processos')
        ->skip('Falta implementar um get detalhes do processo ai vem os envolvidos');

})->group('Integracao', 'Processos');