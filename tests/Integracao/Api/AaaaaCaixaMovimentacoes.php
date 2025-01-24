<?php

use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;

global $jwt;

if(!is_file(__DIR__.'/../../../.env')) {
    return;
}

beforeEach(function(){

	$this->clientHTTPAuth = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8052'
	]);

	$this->clientHTTPApi = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8053'
	]);
});

describe('(Caixa Movimentacoes):', function() use (&$jwt) {

    it('Deverá retornar as movimentações da conta bancária.', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/contasbancarias');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(1)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0]['nome'])->toContain('Conta Principal');

        $contaBancaria = $resposta->body[0];

        $resposta = $this->clientHTTPApi->get('/financeiro/movimentacoes/'.$contaBancaria['codigo']);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(3)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigoMovimentacao')
            ->and($resposta->body[0])->toHaveKey('planoDeContaCodigo')
            ->and($resposta->body[0])->toHaveKey('dataMovimentacao')
            ->and($resposta->body[0])->toHaveKey('descricao')
            ->and($resposta->body[0])->toHaveKey('valor');
    })
        ->group('Integracao', 'Caixa Movimentacoes')
        ->skip('SKIP TEMPORARIO: Deverá retornar as movimentações da conta bancária.');
})
    ->group('Integracao', 'Caixa Movimentacoes');