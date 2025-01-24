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

describe('(Modelo Documentos):', function() use (&$jwt) {

    it("Deverá criar um modelo de documento.", function () use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->upload('/modelos', [
            'files' => [
                __DIR__.'/modelo_declaracao.docx',
            ],
            'nome' => 'Modelo de Documento',
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Modelo de documento cadastrado com sucesso');

    });

    it("Deverá retornar uma lista de documentos.", function () use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/modelos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(3)
            ->and($resposta->body[0])->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0]['nome'])->toBe('Declaração de Residência');

    });

    it("Atualizar um modelo de documento.", function () use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/modelos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(3);

        $modelo = $resposta->body[0];

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->upload('/modelos', [
            'files' => [
                __DIR__.'/modelo_declaracao.docx',
            ],
            'codigo' => $modelo['codigo'],
            'nome' => 'Titulo Modelo de Documento Atualizado',
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Modelo de documento cadastrado com sucesso');

    })->group('Integracao', 'Modelos')
        ->skip('Rever isso, ele esta criando e não atualizando');

    it('Devera retornar o link para visualizar o PDF preview', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
        $resposta = $this->clientHTTPApi->get('/modelos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(3);

        $modelo = $resposta->body[0];

        $resposta = $this->clientHTTPApi->get('/modelos/preview/'.$modelo['codigo']);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('link')
            ->and($resposta->body['link'])->toBeString()
            ->and($resposta->body['link'])->toContain('https://jusizi-para-teste.ac2eb7e5c09270f176d3958a5550eee0.r2.cloudflarestorage.com');
    })
        ;


    it('Devera gerar um documento apartir de um modelo para um cliente e retornar o Link para Download', function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
        $resposta = $this->clientHTTPApi->get('/modelos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(3);

        $modelo = $resposta->body[0];

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        $cliente = array_filter($resposta->body, function ($cliente) {
            return $cliente['documento'] === '619.085.330-72';
        });

        $cliente = array_shift($cliente);

        $resposta = $this->clientHTTPApi->get('/clientes/gerardocumento/?modelo='.$modelo['codigo'].'&cliente='.$cliente['codigo']);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('link')
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['link'])->toBeString()
            ->and($resposta->body['link'])->toContain('https://jusizi-para-teste.ac2eb7e5c09270f176d3958a5550eee0.r2.cloudflarestorage.com')
            ->and($resposta->body['message'])->toBe('Documento gerado com sucesso');

    });

})->group('Integracao', 'Modelos');