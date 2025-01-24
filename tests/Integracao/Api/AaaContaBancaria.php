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

describe('(Conta Bancaria):', function() use (&$jwt) {

    it('Deverá existir uma conta bancária criada no momento da criação da empresa.', function() use (&$jwt) {

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
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('banco')
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste');
    })
        ->group('Integracao', 'ContaBancaria');

    it('Deverá atualizar as informações da conta bancaria principal', function() use(&$jwt){

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
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('banco')
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste');

        $contaBancaria = $resposta->body[0];

        $resposta = $this->clientHTTPApi->put(
            endpoint: '/contasbancarias',
            data: [
                'codigo' => $contaBancaria['codigo'],
                'nome' => 'Conta Principal - Empresa Teste atualizada',
                'chaveAPI' => '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmI2YmI4NWM3LWFkNTYtNGRiNi04YjgzLWI0YTM3MTBjOGRkMjo6JGFhY2hfNzUxMGY4ZmYtYTFhMS00NTgzLWJhMDEtYmQ1ODBlZGRmZDMz',
                'clientID' => 'c819cbad-7dcd-4ffb-ab57-3e961bcea57e',
                'ambiente' => 'Producao'
            ]
        );

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('As informações da conta bancária foram atualizadas com sucesso');
    })
        ->group('Integracao', 'ContaBancaria');

    it('As informações da conta bancaria principal precisam estar atualizadas', function() use(&$jwt){

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
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('ambiente')
            ->and($resposta->body[0])->toHaveKey('banco')
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste atualizada')
            ->and($resposta->body[0]['ambiente'])->toBe('Producao')
            ->and($resposta->body[0]['chaveAPI'])->toBe('****************')
            ->and($resposta->body[0]['clientID'])->toBe('c819cbad-7dcd-4ffb-ab57-3e961bcea57e');
    })
        ->group('Integracao', 'ContaBancaria');

    // Vamos deixar essa conta bancaria como Sandbox.
    it('Vamos atualizar o ambiente da conta bancaria de Producao para Sandbox', function() use(&$jwt){

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
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('ambiente')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('banco')
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste atualizada')
            ->and($resposta->body[0]['ambiente'])->toBe('Producao');

        $contaBancaria = $resposta->body[0];

        $resposta = $this->clientHTTPApi->put(
            endpoint: '/contasbancarias',
            data: [
                'codigo' => $contaBancaria['codigo'],
                'nome' => $contaBancaria['nome'],
                'chaveAPI' => $contaBancaria['chaveAPI'],
                'clientID' => $contaBancaria['clientID'],
                'ambiente' => 'Sandbox'
            ]
        );

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('As informações da conta bancária foram atualizadas com sucesso');

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
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('chaveAPI')
            ->and($resposta->body[0])->toHaveKey('ambiente')
            ->and($resposta->body[0])->toHaveKey('clientID')
            ->and($resposta->body[0])->toHaveKey('banco')
            ->and($resposta->body[0]['nome'])->toBe('Conta Principal - Empresa Teste atualizada')
            ->and($resposta->body[0]['ambiente'])->toBe('Sandbox');

    })
        ->group('Integracao', 'ContaBancaria');
})
    ->group('Integracao', 'ContaBancaria');