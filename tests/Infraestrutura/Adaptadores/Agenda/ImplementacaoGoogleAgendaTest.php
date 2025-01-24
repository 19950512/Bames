<?php

use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Infraestrutura\Adaptadores\Cache\ImplementacaoCacheMemoria;
use App\Infraestrutura\Adaptadores\Agenda\ImplementacaoGoogleAgenda;
use App\Infraestrutura\Adaptadores\Ambiente\ImplementacaoAmbienteArquivo;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;


$ambiente = new ImplementacaoAmbienteArquivo();

$entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado(new SaidaFronteiraBuscarContaPorCodigo(
    empresaCodigo: (new IdentificacaoUnica())->get(),
    contaCodigo: (new IdentificacaoUnica())->get(),
    nomeCompleto: 'Matheus Maydana',
    email: 'meu@email.com',
    documento: '17816441010',
    hashSenha: '',
    oab: '123456',
));

$cache = new ImplementacaoCacheMemoria();

$agenda = new ImplementacaoGoogleAgenda(
    ambiente: $ambiente,
    cache: $cache,
    //codigoAutorizacao: '4/0AdLIrYf6svLebAQTf_QeQhw8rg3q9sapnCKvnJ3UlrxUiyH8zYMgcGN4C_pff1olKt-MKQ'
);

test('O codigo de autorização deverá ser vazio', function() use (&$agenda){
    
    expect($agenda->codigoAutorizacao)->toBeEmpty();
})->group('ImplementacaoGoogleAgenda');

test('O metodo checkCode deverá lançar uma exceção', function() use (&$agenda){

    $agenda->checkCode();
    
})->group('ImplementacaoGoogleAgenda')->throws('Você não forneceu um código de autorização, acesse.');


test('Deverá acessar a URL para gerar o codigo de autorizacao', function() use (&$agenda){

    $agenda->codigoAutorizacao = '4/0AdLIrYf6svLebAQTf_QeQhw8rg3q9sapnCKvnJ3UlrxUiyH8zYMgcGN4C_pff1olKt-MKQ';
    expect($agenda->codigoAutorizacao)->toBe('4/0AdLIrYf6svLebAQTf_QeQhw8rg3q9sapnCKvnJ3UlrxUiyH8zYMgcGN4C_pff1olKt-MKQ');
    
})->group('ImplementacaoGoogleAgenda');

test('Deve retornar a URL de login: '.$agenda->getLoginUrl(), function() use (&$agenda){

    $url = $agenda->getLoginUrl();
    expect($url)->toBeString();
    
})->group('ImplementacaoGoogleAgenda');

test('Deve setar o novo codigoAutorizacao', function() use (&$agenda){
    
    $agenda->codigoAutorizacao = '4/0AdLIrYdtPzxvW5Fit389HMnv0z7-0QysvBRpOSDASBvSIdvGlE1GjvGfSvR_zW1aj4eNqA';

    expect($agenda->codigoAutorizacao)->toBe('4/0AdLIrYdtPzxvW5Fit389HMnv0z7-0QysvBRpOSDASBvSIdvGlE1GjvGfSvR_zW1aj4eNqA');
    
})->group('ImplementacaoGoogleAgenda');


test('Deve setar o accessToken', function() use (&$agenda){
    $agenda->setAccessToken('ya29.a0AXooCgvi_-d1RLrEVXgpXRxSqNYpX184QDApIWZsGUg1aL_B0IkRTBlS3PvH3AhM7UGDK2YaYVR5xNzJDGh4GLhh6FV2FcRJ_dY5r5Q3sTF_25N5Uji1Z1Z3hJBO798WsD-YGII70LiEo-Uto24RSlCLbGXm8Dm49psaCgYKAaUSARISFQHGX2Mi3lRnA7VXwmT9ZdM2slLkVw0170');
    expect($agenda->_getAccessToken())->toBe('ya29.a0AXooCgvi_-d1RLrEVXgpXRxSqNYpX184QDApIWZsGUg1aL_B0IkRTBlS3PvH3AhM7UGDK2YaYVR5xNzJDGh4GLhh6FV2FcRJ_dY5r5Q3sTF_25N5Uji1Z1Z3hJBO798WsD-YGII70LiEo-Uto24RSlCLbGXm8Dm49psaCgYKAaUSARISFQHGX2Mi3lRnA7VXwmT9ZdM2slLkVw0170');

})->group('ImplementacaoGoogleAgenda');

test('Deve retornar um array com os eventos da agenda', function() use (&$agenda){

    $eventos = $agenda->listarEventos();
    expect($eventos)->toBeArray();
    
})->group('ImplementacaoGoogleAgenda')->skip('Isse teste não sei fazer ainda, por conta de precisa de um "navegador" para fazer a requisição.');