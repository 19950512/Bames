<?php

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Infraestrutura\Adaptadores\Email\PHPMailerEmail;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;

test('Deverá enviar um e-mail com sucesso', function(){
    
    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('EMAIL_ENVIAR')
        ->andReturn(true)
        ->getMock()

        ->shouldReceive('get')
        ->with('EMAIL_HOST')
        ->andReturn('smtpi.kinghost.net')
        ->getMock()

        ->shouldReceive('get')
        ->with('EMAIL_USERNAME')
        ->andReturn('contato@jusizi.com.br')
        ->getMock()

        ->shouldReceive('get')
        ->with('EMAIL_PASSWORD')
        ->andReturn('89578779Aa!')
        ->getMock()

        ->shouldReceive('get')
        ->with('EMAIL_PORT')
        ->andReturn(587)
        ->getMock()
        
        ->shouldReceive('get')
        ->with('EMAIL_REMETENTE')
        ->andReturn('contato@jusizi.com.br')
        ->getMock()
        
        ->shouldReceive('get')
        ->with('EMAIL_REMETENTE_NOME')
        ->andReturn('Jusizi')
        ->getMock();

    $email = new PHPMailerEmail($this->ambiente);

    $params = new EntradaFronteiraEnviarEmail(
        destinatarioEmail: 'mattmaydana@gmail.com',
        destinatarioNome: 'Matheus Maydana',
        assunto: 'E-mail teste PHPMailer - Jusizi - teste automatizado',
        mensagem: 'E-mail teste PHPMailer - Jusizi - teste automatizado'
    );
    
    $resposta = $email->enviar($params);

    expect($resposta->emailCodigo)->not()->toBeNull();
    expect($resposta->emailCodigo)->not()->toBeEmpty();
    expect($resposta->emailCodigo)->toBeString();

})->group('PHPMailerEmailTest');