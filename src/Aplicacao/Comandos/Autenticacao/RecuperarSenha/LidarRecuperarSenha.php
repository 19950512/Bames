<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\RecuperarSenha;

use App\Dominio\ObjetoValor\AccessToken;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\Entidades\JusiziEntity;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use function DI\get;

final readonly class LidarRecuperarSenha implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private RepositorioRequest $repositorioRequest,
        private RepositorioEmail $repositorioEmail,
        private JusiziEntity $jusiziEntity,
        private Email $email
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoRecuperarSenha::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $email = $comando->obterEmail();

        try {

            $contaData = $this->repositorioAutenticacaoComando->buscarContaPorEmail(
                email: $email,
            );

            $usuarioSistema = UsuarioSistema::build($contaData);

        }catch (Exception $erro){

            if(str_contains($erro->getMessage(), 'A conta não existe na base de dados com esse')){
                throw new Exception("Não foi possível encontrar a conta com o e-mail informado.");
            }

            throw new Exception("E-mail não encontrado.");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: (new AccessToken(''))
        );

        if(!empty($usuarioSistema->tokenParaRecuperarSenha)){

            $novoEventoRequest = new Evento("Tentativa de recuperação de senha já solicitada. (E-mail: $email, Empresa: {$usuarioSistema->empresaCodigo->get()})");
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
            throw new Exception("Já enviamos para seu e-mail um token para recuperação.");
        }

        $tokenRecuperarSenha = new IdentificacaoUnica();

        try {

            $this->repositorioAutenticacaoComando->salvaTokenParaRecuperacaoDeSenha(
                tokenRecuperarSenha: $tokenRecuperarSenha->get(),
                empresaCodigo: $usuarioSistema->empresaCodigo->get(),
                contaCodigo: $usuarioSistema->codigo->get(),
                contaEmail: $usuarioSistema->email->get(),
            );

            $linkParaAlteracaoDeSenha = "https://www.suaaplicacao.com.br/recuperar-senha?token={$tokenRecuperarSenha->get()}";

            $bodyHtmlEmail = <<<bodyEmail
            <h3>Esqueceu sua senha?</h3>
            <p>Utilize seu código secreto para redefini-la!</p>
            <br />
            <h1>{$tokenRecuperarSenha->get()}</h1>
            <br />
            <p>Clique no botão abaixo e insira o código secreto acima.</p>
            <br />
            <p><a href="{$linkParaAlteracaoDeSenha}" target="_blank">Alterar senha</a></p>
            <br />
            <p>Se você não solicitou a redefinição da sua senha, pode ignorar este e-mail.</p>
           bodyEmail;

            $tituloEmail = "Redefina sua senha agora! - {$this->jusiziEntity->fantasia}";

            $parametrosEnviarEmail = new EntradaFronteiraEnviarEmail(
                destinatarioEmail: $usuarioSistema->email->get(),
                destinatarioNome: $usuarioSistema->nomeCompleto->get(),
                assunto: $tituloEmail,
                mensagem: $bodyHtmlEmail,
            );

            $emailCodigo = $this->email->enviar($parametrosEnviarEmail);

            $novoEventoRequest = new Evento("E-mail de recuperação de senha enviado com sucesso. (E-mail ID: {$emailCodigo->emailCodigo} - Destinatário e-mail: {$usuarioSistema->email->get()}, Destinatário nome: {$usuarioSistema->nomeCompleto->get()}) (Empresa: {$usuarioSistema->empresaCodigo->get()} ID: {$usuarioSistema->codigo->get()}, E-mail Codigo: {$emailCodigo->emailCodigo})");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosEmailEnviado = new FronteiraEntradaSalvarEmailEnviado(
                emailCodigo: $emailCodigo->emailCodigo,
                assunto: $tituloEmail,
                mensagem: $bodyHtmlEmail,
                destinatarioNome: $usuarioSistema->nomeCompleto->get(),
                destinatarioEmail: $usuarioSistema->email->get(),
                empresaID: $usuarioSistema->empresaCodigo->get(),
                situacao: 'Enviado'
            );
            $this->repositorioEmail->salvarEmailEnviado($parametrosEmailEnviado);

            return NULL;

        } catch (Exception $erro) {

            $novoEventoRequest = new Evento("Erro ao enviar e-mail de recuperação de senha. (E-mail: $email, Empresa: {$usuarioSistema->empresaCodigo->get()} - Erro: {$erro->getMessage()})");
            $eventosDoRequest->adicionar($novoEventoRequest);

            throw new Exception("E-mail não encontrado.");

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
    }
}