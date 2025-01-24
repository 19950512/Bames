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

	$this->clientHTTPWebhook = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8054'
	]);
});

describe('(Boleto):', function() use (&$jwt) {

    it('Deverá consultar um boleto na plataforma de cobranca e estar como aguardando_pagamento.', function() use (&$jwt) {

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

        $boletoCodigo = $resposta->body[0]['boletos'][0]['boletoCodigo'];

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/cobranca/consultarboleto/'.$boletoCodigo);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body)->toHaveKey('status')
            ->and($resposta->body['message'])->toBe('O boleto foi consultado com sucesso.')
            ->and($resposta->body['status'])->toBe('aguardando_pagamento');

    })
        ->group('Integracao', 'Cobranca')
        ->skip('Esse teste precisa ser atualizado, pois agora para descobrir os boletos da cobranca, é necessário consultar a cobranca pelo codigo.');


    it('Receberá um webhook de que o boleto foi aceito na plataforma', function() use (&$jwt) {

        // Vamos pegar o empresaCodigo.
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/empresa');

        $empresaCodigo = $resposta->body['codigo'];

        $this->clientHTTPWebhook->configurar([
            'headers' => [
                'user-agent: AsaasWebhook',
            ]
        ]);

        $webhookDados = json_decode(file_get_contents(__DIR__.'/Webhooks/boleto_registrado.json'), true);

        $retorno = $this->clientHTTPWebhook->post('/asaas?empresaCodigo='.$empresaCodigo, $webhookDados);

        expect($retorno->code)->toBe(200)
            ->and($retorno->body)->toBeArray()
            ->and($retorno->body)->toHaveKey('message')
            ->and($retorno->body['message'])->toBe('ok');
    })
        ->group('Integracao', 'Cobranca')
        ->skip('SKIP TEMPORARIO: Esta funcionando, mas vamos pular para testar o boleto pago.');

    it('Deverá consultar um boleto na plataforma de cobranca e estar como registrado.', function() use (&$jwt) {

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
            ->and($resposta->body[0])->toHaveKey('contaBancariaCodigo')
            ->and($resposta->body[0])->toHaveKey('clienteCodigo')
            ->and($resposta->body[0])->toHaveKey('composicaoDaCobranca')
            ->and($resposta->body[0])->toHaveKey('boletos')
            ->and($resposta->body[0]['composicaoDaCobranca'])->toBeArray()
            ->and($resposta->body[0]['composicaoDaCobranca'])->toHaveCount(2)
            ->and($resposta->body[0]['boletos'])->toBeArray()
            ->and($resposta->body[0]['boletos'])->toHaveCount(1)
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('boletoCodigo')
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('status')
            ->and($resposta->body[0]['boletos'][0]['status'])->toBe('Registrado');
    })
        ->group('Integracao', 'Cobranca')
        ->skip('Esse teste precisa ser atualizado, pois agora para descobrir os boletos da cobranca, é necessário consultar a cobranca pelo codigo.');


    it('Receberá um webhook de que o boleto foi pago.', function() use (&$jwt) {

        // Vamos pegar o empresaCodigo.
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/empresa');

        $empresaCodigo = $resposta->body['codigo'];

        $this->clientHTTPWebhook->configurar([
            'headers' => [
                'user-agent: AsaasWebhook',
            ]
        ]);

        $body = json_decode(file_get_contents(__DIR__.'/Webhooks/boleto_pago.json'), true);
        $retorno = $this->clientHTTPWebhook->post('/asaas?empresaCodigo='.$empresaCodigo, $body);

        expect($retorno->code)->toBe(200)
            ->and($retorno->body)->toBeArray()
            ->and($retorno->body)->toHaveKey('message')
            ->and($retorno->body['message'])->toBe('ok');
    })
        ->group('Integracao', 'Cobranca');


    it('Deverá consultar um boleto na plataforma de cobranca e estar como pago.', function() use (&$jwt) {

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
            ->and($resposta->body[0])->toHaveKey('contaBancariaCodigo')
            ->and($resposta->body[0])->toHaveKey('clienteCodigo')
            ->and($resposta->body[0])->toHaveKey('composicaoDaCobranca')
            ->and($resposta->body[0])->toHaveKey('boletos')
            ->and($resposta->body[0]['composicaoDaCobranca'])->toBeArray()
            ->and($resposta->body[0]['composicaoDaCobranca'])->toHaveCount(2)
            ->and($resposta->body[0]['boletos'])->toBeArray()
            ->and($resposta->body[0]['boletos'])->toHaveCount(1)
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('boletoCodigo')
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('status')
            ->and($resposta->body[0]['boletos'][0]['status'])->toBe('Pago');
    })
        ->group('Integracao', 'Cobranca')
        ->skip('Esse teste precisa ser atualizado, pois agora para descobrir os boletos da cobranca, é necessário consultar a cobranca pelo codigo.');


    it('Receberá um webhook de que o boleto de parcelamento foi aceito na plataforma', function() use (&$jwt) {


        // Vamos identificar o codigo do boleto no sistema que foi emitido anteriormente.

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
            ->and($resposta->body[0])->toHaveKey('contaBancariaCodigo')
            ->and($resposta->body[0])->toHaveKey('clienteCodigo')
            ->and($resposta->body[0])->toHaveKey('composicaoDaCobranca')
            ->and($resposta->body[0])->toHaveKey('codigoNaPlataformaCobrancaAPI')
            ->and($resposta->body[0])->toHaveKey('boletos')
            ->and($resposta->body[0]['composicaoDaCobranca'])->toBeArray()
            ->and($resposta->body[0]['composicaoDaCobranca'])->toHaveCount(3)
            ->and($resposta->body[0]['boletos'])->toBeArray()
            ->and($resposta->body[0]['boletos'])->toHaveCount(1)
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('boletoCodigo')
            ->and($resposta->body[0]['boletos'][0])->toHaveKey('boletoCodigoNaPlataformaCobrancaAPI');

        $boletoCodigoNaPlataformaCobrancaAPI = $resposta->body[0]['boletos'][0]['boletoCodigoNaPlataformaCobrancaAPI'];
        $cobrancaCodigoNaPlataformaCobrancaAPI = $resposta->body[0]['codigoNaPlataformaCobrancaAPI'];

        // Vamos pegar o empresaCodigo.
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/empresa');

        $empresaCodigo = $resposta->body['codigo'];

        $this->clientHTTPWebhook->configurar([
            'headers' => [
                'user-agent: AsaasWebhook',
            ]
        ]);

        $webhookConteudo = file_get_contents(__DIR__.'/Webhooks/boleto_parcelamento_registrato.json');
        $mustache = [
            '{{codigo_boleto_na_plataforma}}' => $boletoCodigoNaPlataformaCobrancaAPI,
            '{{codigo_cobranca_na_plataforma}}' => $cobrancaCodigoNaPlataformaCobrancaAPI
        ];
        $webhookConteudo = str_replace(array_keys($mustache), array_values($mustache), $webhookConteudo);

        $webhookDados = json_decode($webhookConteudo, true);

        $retorno = $this->clientHTTPWebhook->post('/asaas?empresaCodigo='.$empresaCodigo, $webhookDados);

        expect($retorno->code)->toBe(200)
            ->and($retorno->body)->toBeArray()
            ->and($retorno->body)->toHaveKey('message')
            ->and($retorno->body['message'])->toBe('ok');
    })
        ->group('Integracao', 'Cobranca')
        ->skip('Esse teste precisa ser atualizado, pois agora para descobrir os boletos da cobranca, é necessário consultar a cobranca pelo codigo.');

})
    ->group('Integracao', 'Cobranca');