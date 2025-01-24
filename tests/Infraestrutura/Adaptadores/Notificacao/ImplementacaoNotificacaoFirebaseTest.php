<?php

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Notificacao\Notificacao;
use App\Infraestrutura\Adaptadores\Notificacao\ImplementacaoNotificacaoFirebase;

beforeEach(function () {
    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('GOOGLE_AGENDA_CLIENT_REDIRECT_URL')
        ->andReturn('12321')
        ->getMock();
    $this->implementacaoNotificacaoFirebase = new ImplementacaoNotificacaoFirebase(
        ambiente: $this->ambiente
    );
});

test("Deverá ser uma instância de ImplementacaoNotificacaoFirebase", function () {
    expect($this->implementacaoNotificacaoFirebase)->toBeInstanceOf(ImplementacaoNotificacaoFirebase::class)
        ->and($this->implementacaoNotificacaoFirebase)->toBeInstanceOf(Notificacao::class);
})->group('NotificacaoFirebase');

test("Deverá retornar o caminho das credenciais do Firebase - '/Aplicacao/Compartilhado/Credenciais/firebase-admin-sdk.json'", function () {
    $obterODiretorioDasCredenciais = $this->implementacaoNotificacaoFirebase->obterODiretorioDasCredenciais();
    expect($obterODiretorioDasCredenciais)->toContain('/Aplicacao/Compartilhado/Credenciais/firebase-admin-sdk.json');
})->group('NotificacaoFirebase');

test("Deverá lançar uma exceção que o token FCM está inválido.", function () {
    $titulo = 'Título da notificação';
    $mensagem = 'Mensagem da notificação';
    $fcmToken = 'tokeninvalido';
    $this->implementacaoNotificacaoFirebase->enviar($titulo, $mensagem, $fcmToken);
})
    ->throws('Ops, não é possível enviar notificação pois o FCM Token informado não é válido.')
    ->group('NotificacaoFirebase');

test("Deverá enviar a notificação com sucesso sem erros.", function () {
    $titulo = 'Título da notificação';
    $mensagem = 'Mensagem da notificação';
    $fcmToken = 'jajajaja';
    $this->implementacaoNotificacaoFirebase->enviar($titulo, $mensagem, $fcmToken);
    expect(true)->toBeTrue();
})
    //->throws('Ops, não é possível enviar notificação pois o FCM Token informado não foi encontrado.')
    ->group('NotificacaoFirebase');
