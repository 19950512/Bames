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

describe('(Plano de Contas):', function() use (&$jwt) {

    it('Deverá retornar uma lista com todos os 51 planos de contas', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/planosdecontas');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(51)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('tipo')
            ->and($resposta->body[0])->toHaveKey('categoria')
            ->and($resposta->body[0])->toHaveKey('descricao')
            ->and($resposta->body[0])->toHaveKey('codigoPlanoDeContasPai')
            ->and($resposta->body[0])->toHaveKey('nivel');

    })
        ->group('Integracao', 'Plano de Contas');

    it('Deverá retornar os planos de contas agrupados', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/planosdecontas/agrupados');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(11)
            ->and($resposta->body['Receitas'])->toBeArray()
            ->and($resposta->body['Receitas'])->toHaveCount(4)
            ->and($resposta->body['Despesas'])->toBeArray()
            ->and($resposta->body['Despesas'])->toHaveCount(5)
            ->and($resposta->body['Despesas Operacionais'])->toBeArray()
            ->and($resposta->body['Despesas Operacionais'])->toHaveCount(8);

    })
        ->group('Integracao', 'Plano de Contas');
})
    ->group('Integracao', 'Plano de Contas');