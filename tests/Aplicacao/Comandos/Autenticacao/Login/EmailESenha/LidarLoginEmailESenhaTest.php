<?php

declare(strict_types=1);

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Token;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha\LidarLoginEmailESenha;
use App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha\ComandoLoginEmailESenha;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

beforeEach(function(){

    $this->repositorioAutenticacao = Mockery::mock(RepositorioAutenticacao::class)
        ->shouldReceive('novaConta')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('novoToken')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('obterOMotivoDoBloqueioDaConta')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('buscarContaPorEmail')
        ->andReturn(new SaidaFronteiraBuscarContaPorCodigo(
            empresaCodigo: (new IdentificacaoUnica())->get(),
            contaCodigo: (new IdentificacaoUnica())->get(),
            nomeCompleto: 'Matheus Maydana',
            email: 'email@para.teste',
            documento: '84167670097',
            emailVerificado:true,
            hashSenha: password_hash('123456789', PASSWORD_ARGON2I),
            oab: 'RS 123456',
            tokenParaRecuperarSenha: ''
        ))
        ->getMock();
    
    $this->repositorioRequest = Mockery::mock(RepositorioRequest::class)
        ->shouldReceive('salvarEventosDoRequest')
        ->andReturn('')
        ->getMock();

    $this->token = Mockery::mock(Token::class)
        ->shouldReceive('encode')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('decode')
        ->andReturn([])
        ->getMock();

	$this->discord = Mockery::mock(Discord::class)
    ->shouldReceive('enviar')
    ->andReturn()
    ->getMock();

	$this->comandoFake = Mockery::mock(Comando::class)
    ->shouldReceive('obterEmail')
    ->andReturn('')
    ->getMock();

});

test('LidarLoginEmailESenha só pode lidar com ComandoLoginEmailESenha.', function(){

	$lidarLoginEmailESenha = new LidarLoginEmailESenha(
        repositorioAutenticacaoComando: $this->repositorioAutenticacao,
        repositorioRequest: $this->repositorioRequest,
        discord: $this->discord,
        token: $this->token
    );

	$lidarLoginEmailESenha->lidar($this->comandoFake);

})
    ->throws('Ops, não sei lidar com esse comando.')
	->group('LidarLoginEmailESenha');

test('LidarLoginEmailESenha deve lidar com ComandoLoginEmailESenha com sucesso e retornar um AccessToken', function(){

	$comandoLoginEmailESenha = new ComandoLoginEmailESenha(
		email: 'matheus@email.com',
		senha: '123456789'
	);
    
	$comandoLoginEmailESenha->executar();

	$lidarLoginEmailESenha = new LidarLoginEmailESenha(
        repositorioAutenticacaoComando: $this->repositorioAutenticacao,
        repositorioRequest: $this->repositorioRequest,
        discord: $this->discord,
        token: $this->token
    );

	expect($lidarLoginEmailESenha->lidar($comandoLoginEmailESenha))->toBeInstanceOf(AccessToken::class);
})
	->group('LidarLoginEmailESenha');