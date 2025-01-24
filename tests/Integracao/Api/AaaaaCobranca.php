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

describe('(Cobranca Simples):', function() use (&$jwt) {

    it('Deverá criar uma cobrança para um cliente no valor de 42,00.', function() use (&$jwt) {

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
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste atualizada');

        $contaBancaria = $resposta->body[0];


        $resposta = $this->clientHTTPApi->post('/clientes/consultarinformacoesnainternet', [
            'documento' => '84167670097'
        ]);

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nomeCompleto');

        $cliente = $resposta->body[0];

        $contaBancariaCodigo = $contaBancaria['codigo'];
        $clienteCodigo = $cliente['codigo'];

        $resposta = $this->clientHTTPApi->post('/cobranca',[
            'clienteCodigo' => $clienteCodigo,
            'descricao' => 'Cobrança de teste - descrição da boa',
            'dataVencimento' => date('Y-m-d', strtotime('+6 day')),
            'meioDePagamento' => 'Boleto',
            'juros' => 1,
            'multa' => 2,
            'parcelas' => 1,
            'contaBancariaCodigo' => $contaBancariaCodigo,
            'composicaoDaCobranca' => [
                [
                    'descricao' => 'Descricao do item aqui',
                    'planoDeContaCodigo' => 1,
                    'valor' => 21.00
                ],
                [
                    'descricao' => 'Descricao do item aqui doisX',
                    'planoDeContaCodigo' => 2,
                    'valor' => 21.00
                ]
            ]
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Cobrança realizada com sucesso');
    })
        ->group('Integracao', 'Cobranca');

    it('Deverá consultar as cobranças e terá que existir 1 cobrança.', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/cobranca');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(1)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('dataVencimento')
            ->and($resposta->body[0])->toHaveKey('pagadorNomeCompleto')
            ->and($resposta->body[0])->toHaveKey('descricao')
            ->and($resposta->body[0])->toHaveKey('valor')
            ->and($resposta->body[0])->toHaveKey('meioDePagamentoName');
    })
        ->group('Integracao', 'Cobranca');

})
    ->group('Integracao', 'Cobranca');

describe('(Cobranca Parcelada):', function() use (&$jwt) {

    it('Deverá criar uma cobrança para um cliente no valor de 1453.00 e fazer em 3 parcelas de 484.33.', function() use (&$jwt) {

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
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste atualizada');

        $contaBancaria = $resposta->body[0];

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nomeCompleto');

        $cliente = $resposta->body[0];

        $contaBancariaCodigo = $contaBancaria['codigo'];
        $clienteCodigo = $cliente['codigo'];

        $resposta = $this->clientHTTPApi->post('/cobranca',[
            'clienteCodigo' => $clienteCodigo,
            'descricao' => 'Soraka - A mulher que cura',
            'dataVencimento' => date('Y-m-d', strtotime('+10 day')),
            'meioDePagamento' => 'Boleto',
            'juros' => 1,
            'multa' => 2,
            'parcelas' => 3,
            'contaBancariaCodigo' => $contaBancariaCodigo,
            'composicaoDaCobranca' => [
                [
                    'descricao' => '',
                    'planoDeContaCodigo' => 3,
                    'valor' => 78.00
                ],
                [
                    'descricao' => 'É o Judaz',
                    'planoDeContaCodigo' => 4,
                    'valor' => 478.00
                ],
                [
                    'descricao' => 'Algum item ai',
                    'planoDeContaCodigo' => 7,
                    'valor' => 897.00
                ]
            ]
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Cobrança realizada com sucesso');
    })
        ->group('Integracao', 'Cobranca');

    it('Deverá consultar as cobranças e terá que existir 2 cobrança.', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/cobranca');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(2)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('dataVencimento')
            ->and($resposta->body[0])->toHaveKey('pagadorNomeCompleto')
            ->and($resposta->body[0])->toHaveKey('descricao')
            ->and($resposta->body[0])->toHaveKey('valor')
            ->and($resposta->body[0])->toHaveKey('meioDePagamentoName');
    })
        ->group('Integracao', 'Cobranca');

})
    ->group('Integracao', 'Cobranca');
