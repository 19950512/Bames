<?php

use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;

global $jwt;

if(!is_file(__DIR__.'/../../../.env')) {
    return;
}

beforeEach(function(){

    $this->clientHTTPApi = new ImplementacaoCurlClienteHTTP([
        'baseURL' => 'http://localhost:8053'
    ]);
});

describe('(Consulta OAB)', function() use (&$jwt){

    it('Deverá consultar uma OAB e salvar os processos da OAB', function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->get('/oab/consultar/RS109291');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Consulta realizada com sucesso');

    })->group('Integracao','ConsultaOAB','OAB');

    it('Deverá acusar erro: Estado da OAB inválido. - Estado precisa ser informado', function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/oab/consultar/109291');

        expect($resposta->code)->toBe(400)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Estado da OAB inválido. - Estado precisa ser informado');

    })->group('Integracao','ConsultaOAB','OAB');

})->group('Integracao', 'ConsultaOAB', 'OAB');