<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\AlterarSenha;

use App\Dominio\ObjetoValor\AccessToken;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\Entidades\JusiziEntity;
use App\Dominio\Entidades\UsuarioSistema;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Aplicacao\Comandos\Autenticacao\AlterarSenha\ComandoAlterarSenha;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

final readonly class LidarAlterarSenha implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private RepositorioEmail $repositorioEmail,
        private Email $email,
        private Ambiente $ambiente,
        private RepositorioRequest $repositorioRequest,
        private JusiziEntity $jusiziEntity,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
        
        if (!is_a($comando, ComandoAlterarSenha::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $novaSenha = $comando->obterSenha();
        $tokenRecuperacaoSenha = $comando->obterToken();

        try {

            $contaData = $this->repositorioAutenticacaoComando->buscarContaPorTokenRecuperacaoDeSenha(
                tokenRecuperarSenha: $tokenRecuperacaoSenha,
            );

            $usuarioSistema = UsuarioSistema::build($contaData);

            $eventosDoRequest = new EventosDoRequest(
                empresaCodigo: $usuarioSistema->empresaCodigo,
                usuarioCodigo: $usuarioSistema->codigo,
                accessToken: new AccessToken('')
            );

            $usuarioSistema->gerarNovaHashDaSenha($novaSenha);
            
            $this->repositorioAutenticacaoComando->atualizarSenhaDoUsuarioSistema(
                contaUsuarioHASHSenha: $usuarioSistema->hashSenha,
                contaUsuarioCodigo: $usuarioSistema->codigo->get(),
                empresaCodigo: $usuarioSistema->empresaCodigo->get()
            );

            $novoEventoRequest = new Evento("O usuário {$usuarioSistema->nomeCompleto->get()} alterou sua senha. (ID: {$usuarioSistema->codigo->get()}, Email: {$usuarioSistema->email->get()}, Empresa: {$usuarioSistema->empresaCodigo->get()})");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $assuntoEmail = 'Sua senha foi alterada com sucesso! - '.$this->jusiziEntity->fantasia;
            $mensagemEmail = <<<htmlMensagemEmail
            <p>Olá {$usuarioSistema->nomeCompleto->get()},</p>
            <br />
            <p>Venho lhe informar que a alteração de sua senha foi realizada com sucesso!</p>
            <br />
            <p>Cumprimentos,</p>
            
            {$this->jusiziEntity->responsavelNome}<br/>
            {$this->jusiziEntity->responsavelCargo}<br/>
            {$this->jusiziEntity->fantasia}<br/>
            htmlMensagemEmail;

            $parametrosEnviarEmail = new EntradaFronteiraEnviarEmail(
                destinatarioEmail: $usuarioSistema->email->get(),
                destinatarioNome: $usuarioSistema->nomeCompleto->get(),
                assunto: $assuntoEmail,
                mensagem: $mensagemEmail
            );

            $emailCodigo = $this->email->enviar($parametrosEnviarEmail);

            $novoEventoRequest = new Evento("E-mail de alteração de senha enviado com sucesso. (E-mail ID: {$emailCodigo->emailCodigo} - Destinatário e-mail: {$usuarioSistema->email->get()}, Destinatário nome: {$usuarioSistema->nomeCompleto->get()}) (Empresa: {$usuarioSistema->empresaCodigo->get()} ID: {$usuarioSistema->codigo->get()}");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosEmailEnviado = new FronteiraEntradaSalvarEmailEnviado(
                emailCodigo: $emailCodigo->emailCodigo,
                assunto: $assuntoEmail,
                mensagem: $mensagemEmail,
                destinatarioNome: $usuarioSistema->nomeCompleto->get(),
                destinatarioEmail: $usuarioSistema->email->get(),
                empresaID: $usuarioSistema->empresaCodigo->get(),
                situacao: 'Enviado'
            );

            $this->repositorioEmail->salvarEmailEnviado($parametrosEmailEnviado);

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
            
            return NULL;

        }catch (Exception $erro){

            if(str_contains($erro->getMessage(), 'A conta não existe na base de dados com esse')){
                throw new Exception("Não foi possível encontrar a conta com o token informado.");
            }

            throw new Exception("Token não encontrado.");
        }
    }
}