<?php

use App\Dominio\ObjetoValor\IdentificacaoUnica;
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

$eventoID = '';
describe('(Agenda): Eventos', function() use (&$jwt){

    it('Deverá criar um evento na agenda', function() use (&$jwt, &$eventoID){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->post('/agenda/evento', [
            "titulo" => "Evento de teste",
            "descricao" => "Descrição do evento de teste",
            "diaTodo" => false,
            "recorrencia" => 0,
            "horarioEventoInicio" => date('Y-m-d H:i:s', strtotime('now')),
            "horarioEventoFim" => date('Y-m-d H:i:s', strtotime('now + 1 hour'))
        ]);

        $eventoID = $resposta->body['eventoID'];

        expect($resposta->code)->toBe(201)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body)->toHaveKey('eventoID')
            ->and($resposta->body['message'])->toBe('Evento cadastrado com sucesso')
            ->and(new IdentificacaoUnica($resposta->body['eventoID']))->toBeInstanceOf(IdentificacaoUnica::class);

    })->group('Integracao','Agenda','Evento');

    it('O eventoID criado deverá ser válido.', function() use (&$eventoID){
        expect($eventoID)->not->toBeEmpty()->and($eventoID)->toBeString()
        ->and(new IdentificacaoUnica($eventoID))->toBeInstanceOf(IdentificacaoUnica::class);
    })->group('Integracao','Agenda','Evento');

    it('Deverá atualizar um evento adicionando uma nova descrição.', function() use (&$jwt, &$eventoID){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->put('/agenda/evento', [
            "eventoID" => $eventoID,
            "titulo" => "Evento de teste - Atualizado",
            "descricao" => "Descrição do evento de teste atualizada",
            "diaTodo" => false,
            "recorrencia" => 0,
            "horarioEventoInicio" => date('Y-m-d H:i:s', strtotime('now')),
            "horarioEventoFim" => date('Y-m-d H:i:s', strtotime('now + 1 hour'))
        ]);

        expect($resposta->code)->toBe(201)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Evento foi atualizado com sucesso');

    })->group('Integracao','Agenda','Evento');

    it('Deverá retornar a lista de meus compromissos', function() use (&$jwt, &$eventoID){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->put('/agenda/meuscompromissos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(1)
            ->and($resposta->body[0])->toHaveKey('codigo');

    })->group('Integracao','Agenda','Evento');

    it('Deverá retornar apenas um compromisso por codigo', function() use (&$jwt, &$eventoID){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->put('/agenda/meuscompromissos');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveCount(1)
            ->and($resposta->body[0])->toHaveKey('codigo');

        $compromissoCodigo = $resposta->body['0']['codigo'];

        $resposta = $this->clientHTTPApi->put('/agenda/compromisso/'.$compromissoCodigo);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray();

    })->group('Integracao','Agenda','Evento');

})->group('Integracao', 'Agenda', 'Evento');