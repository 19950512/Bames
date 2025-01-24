<?php

use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;

global $jwt;
global $email;
global $senha1;

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

describe('(Auth): login', function() use (&$jwt, &$email, &$senha1){

    it("Vamos utilizar nestes testes o usuário teste automatizado. (E-mail: {$email} - Senha: {$senha1})", function() use (&$jwt){
        expect(true)->toBeTrue();
    })->group('Integracao', 'Empresa');

    it('Vamos logar no sistema com o usuario teste automatizado para pegar o JWT.', function() use (&$jwt, &$email, &$senha1){
    
        $resposta = $this->clientHTTPAuth->post('/login', [
            "email" => $email,
            "senha" => $senha1
        ]);
    
        $jwt = $resposta->body['access_token'];
    
        expect($resposta->code)->toBe(201)
            ->and($resposta->body)->toBeArray()
            ->and($jwt)->toBeString();
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá ter criado já um JWT diferente de vazio', function() use (&$jwt){
        expect($jwt)->not->toBeEmpty();
    })->group('Integracao', 'Empresa');

})->group('Integracao', 'Empresa');


$empresaID = '';
describe('(Empresa): empresa', function() use (&$jwt, &$empresaID){

    it('Deverá retornar os dados da empresa', function() use (&$jwt, &$empresaID){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->get('/empresa');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('codigo')
            ->and($resposta->body)->toHaveKey('apelido')
            ->and($resposta->body)->toHaveKey('documentoTipo')
            ->and($resposta->body)->toHaveKey('documentoNumero');

        $empresaID = $resposta->body['codigo'];

    })->group('Integracao', 'Empresa');

})->group('Integracao', 'Empresa');

describe('(Empresa): usuários', function() use (&$jwt){

    it('Deverá listar pelo menos 1 usuario da empresa', function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->get('/empresa/usuarios');
    
        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nome')
            ->and($resposta->body[0])->toHaveKey('email');
    
    })->group('Integracao', 'Empresa');
    
    $emailCadastrar = '';
    it('Deverá cadastrar um novo usuário na empresa', function() use (&$jwt, &$emailCadastrar){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $emailCadastrar = "email-aqui-".rand(111111, 999999)."@gmail.com";
    
        $resposta = $this->clientHTTPApi->post('/empresa/usuarios', [
            "nome" => "Teste Automatizado",
            "email" => $emailCadastrar,
            "oab" => "RS 123".rand(111,999)
        ]);

        expect($resposta->code)->toBe(201)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message');
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá retornar erro ao tentar cadastrar um novo usuário na empresa com email já existente', function() use (&$jwt, &$emailCadastrar){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->post('/empresa/usuarios', [
            "nome" => "Teste Automatizado Versao",
            "email" => $emailCadastrar,
            "oab" => "RS 123".rand(111,999)
        ]);
    
        expect($resposta->code)->toBe(400)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Já existe um colaborador com o e-mail informado.');
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá retornar erro ao tentar cadastrar um novo usuário na empresa sem informar o email', function() use (&$jwt){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->post('/empresa/usuarios', [
            "nome" => "Teste Automatizado Versao",
        ]);
    
        expect($resposta->code)->toBe(400)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('O e-mail precisa ser informado adequadamente.');
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá retornar erro ao tentar cadastrar um novo usuário na empresa sem informar o nome completo', function() use (&$jwt){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->post('/empresa/usuarios', [
            "email" => "email-aqui-".random_int(1, 999999)."@gmail.com",
        ]);
    
        expect($resposta->code)->toBe(400)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('O nome completo precisa ser informado adequadamente.');
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá retornar erro ao tentar cadastrar um novo usuário na empresa sem informar o nome completo e o email', function() use (&$jwt){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->post('/empresa/usuarios', []);
    
        expect($resposta->code)->toBe(400)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('O nome completo precisa ser informado adequadamente.');
    
    })->group('Integracao', 'Empresa');
    
    it('Deverá retornar uma lista com os usuarios da empresa, no mínimo 2', function() use (&$jwt){
    
        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);
    
        $resposta = $this->clientHTTPApi->get('/empresa/usuarios');
    
        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and(count($resposta->body))->toBeGreaterThan(1);
    
    })->group('Integracao', 'Empresa');

})->group('Integracao', 'Empresa');

afterAll(function() use(&$empresaID) {
    echo "Vamos limpar tudo da empresa com ID: $empresaID";
});