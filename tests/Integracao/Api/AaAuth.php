<?php

use App\Dominio\ObjetoValor\CNPJ;
use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;
use App\Infraestrutura\Adaptadores\Ambiente\ImplementacaoAmbienteArquivo;

$CNPJ = CNPJ::gerar();

global $email;
global $senha1;
global $oab;
global $jwt;

if(!is_file(__DIR__.'/../../../.env')) {
    return;
}

$email = 'meus-email-'.rand(str_repeat(1, 16), str_repeat(9, 16)).'@gmail.com';
$senha1 = '0hHMaydana%';
$oab = 'RS '.rand(str_repeat(1, 3), str_repeat(9, 3));
$ambiente = new ImplementacaoAmbienteArquivo();
$desenvolvimento = $ambiente->get('TEST_INTEGRATION_RUN');
$jwt = '';

if(!$desenvolvimento){
    // Não é ambiente de desenvolvimento, não permitir rodar os testes (pelo menos por enquanto.)
    return;
}

beforeEach(function() {
	$this->clientHTTP = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8052'
	]);
});

test("Deverá criar uma empresa com o E-mail: $email CNPJ: $CNPJ e senha: $senha1", function() use ($CNPJ, $email, $senha1, $oab){

	$resposta = $this->clientHTTP->post('/empresa', [
		'nome_fantasia' => 'Empresa Teste',
        'numero_documento' => $CNPJ,
        'oab' => $oab,
		'responsavel_nome_completo' => 'Matheus Maydana',
		'responsavel_email' => $email,
		'responsavel_senha' => $senha1
	]);

	expect($resposta->code)->toBe(201)
		->and($resposta->body)->toBeArray()
		->and($resposta->body['message'])->toBe('Empresa cadastrada com sucesso');
})
	->group('Integracao');


test('Deverá já existir uma empresa com esse documento', function() use ($CNPJ, $email, $senha1, $oab){

	$resposta = $this->clientHTTP->post('/empresa', [
		'nome_fantasia' => 'Empresa Teste',
        'numero_documento' => $CNPJ,
        'oab' => $oab,
		'responsavel_nome_completo' => 'Matheus Maydana',
		'responsavel_email' => $email,
		'responsavel_senha' => $senha1
	]);

	expect($resposta->code)->toBe(400)
		->and($resposta->body)->toBeArray()
		->and($resposta->body['message'])->toBe('Já existe uma empresa com número do documento informado. ('.(new CNPJ($CNPJ))->get().')');
})
	->group('Integracao');

test('Deverá gerar um acess Token - Login efetuado com sucesso', function() use ($email, $senha1, &$jwt){
    
	$resposta = $this->clientHTTP->post('/login', [
        "email" => $email,
        "senha" => $senha1
    ]);

    $jwt = $resposta->body['access_token'];

	expect($resposta->code)->toBe(201)
        ->and($resposta->body)->toBeArray()
        ->and($resposta->body['access_token'])->toBeString();
})
    ->group('Integracao', 'Processos', 'Clientes');


test('Deverá falhar o login - Login e-mail ou senha inválido', function() use ($email, $senha1){
    
	$resposta = $this->clientHTTP->post('/login', [
        "email" => $email,
        "senha" => "{$senha1}42"
    ]);

	expect($resposta->code)->toBe(401)
        ->and($resposta->body)->toBeArray()
        ->and($resposta->body['message'])->toBeString()
        ->and($resposta->body['message'])->toBe('E-mail ou senha inválidos.');
})
    ->group('Integracao');

test('Deverá recuperar a senha da conta', function() use ($email){
    
	$resposta = $this->clientHTTP->post('/recuperar', [
        "email" => $email
    ]);

	expect($resposta->code)->toBe(201)
        ->and($resposta->body)->toBeArray()
        ->and($resposta->body['message'])->toBeString()
        ->and($resposta->body['message'])->toBe('Enviamos um e-mail com as instruções para recuperação de senha.');
})
    ->group('Integracao');

test('Deverá acusar um erro dizendo que já foi feito a solicitação de recuperação e foi enviado por e-mail', function() use ($email){
    
	$resposta = $this->clientHTTP->post('/recuperar', [
        "email" => $email
    ]);

	expect($resposta->code)->toBe(400)
        ->and($resposta->body)->toBeArray()
        ->and($resposta->body['message'])->toBeString()
        ->and($resposta->body['message'])->toBe('Já enviamos para seu e-mail um token para recuperação.');
})
    ->group('Integracao');