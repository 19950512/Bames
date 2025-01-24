<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha;

use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Aplicacao\Compartilhado\Token;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

readonly class LidarLoginEmailESenha implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private RepositorioRequest $repositorioRequest,
        private Discord $discord,
        private Token $token
    ){}

	#[Override] public function lidar(Comando $comando): AccessToken
	{

		if(!is_a($comando, ComandoLoginEmailESenha::class)){
			throw new Exception("Ops, não sei lidar com esse comando.");
		}

		$email = $comando->obterEmail();
		$senha = $comando->obterSenha();

        try {

            $contaData = $this->repositorioAutenticacaoComando->buscarContaPorEmail(
                email: $email,
            );

            $usuarioSistema = UsuarioSistema::build($contaData);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Login,
                mensagem: "Alguém tentou logar com um e-mail que não existe. (E-mail: $email) - {$erro->getMessage()}"
            );
            throw new Exception("E-mail ou senha inválidos.");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: new AccessToken('')
        );

        if(!password_verify($senha, $usuarioSistema->hashSenha)){
            $novoEventoRequest = new Evento("Tentativa de login com e-mail e senha inválidos. (E-mail: $email, Empresa: {$usuarioSistema->empresaCodigo->get()}, SenhaInformada: $senha)");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $usuarioSistema->codigo->get(),
                businessId: $usuarioSistema->empresaCodigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $eventosDoRequest->accessToken->get()
            );
            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Login,
                mensagem: "Alguém tentou logar com um e-mail e senha inválidos. (E-mail: $email)"
            );
            throw new Exception("E-mail ou senha inválidos.");
        }

        if(!$usuarioSistema->emailVerificado){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Login,
                mensagem: "O usuário {$usuarioSistema->nomeCompleto->get()} não verificou o e-mail e está tentando entrar no sistema e foi bloqueado."
            );

            $mensagemResposta = $this->repositorioAutenticacaoComando->obterOMotivoDoBloqueioDaConta(
                empresaCodigo: $usuarioSistema->empresaCodigo->get(),
                usuarioCodigo: $usuarioSistema->codigo->get(),
            );

            if(empty($mensagemResposta)){
                $mensagemResposta = 'Seu e-mail não foi verificado. Acesse sua caixa de entrada e verifique o e-mail que foi enviado a você no cadastro de sua conta.';
            }else{
                $mensagemResposta .= ' - Seu acesso foi bloqueado por segurança, procure o suporte para saber mais.';
            }

            throw new Exception($mensagemResposta);
        }

        try {

            $payload = [
                'id' => $usuarioSistema->codigo->get(),
                'email' => $usuarioSistema->email->get(),
                'name' => $usuarioSistema->nomeCompleto->get(),
                'iat' => time(),
                'exp' => strtotime('+1 day')
            ];

            $accessToken = $this->token->encode($payload);

            $novoEventoRequest = new Evento("O usuário {$usuarioSistema->nomeCompleto->get()} logou no sistema. (ID: {$usuarioSistema->codigo->get()}, Email: {$usuarioSistema->email->get()}, Empresa: {$usuarioSistema->empresaCodigo->get()} - Token: {$accessToken})");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $this->repositorioAutenticacaoComando->novoToken(
                token: $accessToken,
                contaCodigo: $usuarioSistema->codigo->get(),
                empresaCodigo: $usuarioSistema->empresaCodigo->get(),
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Login,
                mensagem: "O usuário {$usuarioSistema->nomeCompleto->get()} logou no sistema. (ID: {$usuarioSistema->codigo->get()}, Email: {$usuarioSistema->email->get()}, Empresa: {$usuarioSistema->empresaCodigo->get()} - Token: {$accessToken})"
            );

        }catch (Exception $erro){

            $novoEventoRequest = new Evento("Erro ao gerar token de acesso. (Erro: {$erro->getMessage()})");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Login,
                mensagem: "Erro ao gerar token de acesso. (Erro: {$erro->getMessage()})"
            );

            throw new Exception("Erro ao gerar token de acesso!!");
        
        }finally{

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $usuarioSistema->codigo->get(),
                businessId: $usuarioSistema->empresaCodigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $eventosDoRequest->accessToken->get()
            );
            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
        }

        return new AccessToken(token: $accessToken);
	}
}