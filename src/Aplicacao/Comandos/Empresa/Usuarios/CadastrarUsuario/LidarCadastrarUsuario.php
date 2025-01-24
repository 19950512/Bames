<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Usuarios\CadastrarUsuario;

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
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Dominio\Repositorios\Empresa\Fronteiras\EntradaFronteiraNovoColaborador;
use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

final readonly class LidarCadastrarUsuario implements Lidar
{

	public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioRequest $repositorioRequest,
        private RepositorioEmail $repositorioEmail,
        private Email $email,
        private Ambiente $ambiente,
        private AccessToken $accessToken,
        private JusiziEntity $jusiziEntity,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoCadastrarUsuario::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $email = $comando->obterEmail();
        $nomeCompleto = $comando->obterNomeCompleto();
        $empresaCodigo = $comando->obterEmpresaCodigo();
        $oab = $comando->obterOAB();

        try {
            $parametrosEmpresa = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($parametrosEmpresa);
        }catch(Exception $erro){
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        $usuarioCodigo = new IdentificacaoUnica();

        $parametrosUsuarioSistema = new SaidaFronteiraBuscarContaPorCodigo(
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            contaCodigo: $usuarioCodigo->get(),
            nomeCompleto: $nomeCompleto,
            email: $email,
            documento: $entidadeEmpresarial->numeroDocumento->get(),
            hashSenha: '',
            oab: $oab
        );
        $usuarioSistema = UsuarioSistema::build($parametrosUsuarioSistema);

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: $this->accessToken
        );

        if($this->repositorioEmpresa->jaExisteUmUsuarioComEsseEmail($email)){

            $novoEventoRequest = new Evento("Tentativa de cadastro de usuário com e-mail já existente. (E-mail: $email, Empresa: $empresaCodigo, Nome: $nomeCompleto)");
            $this->salvaEventosDoRequest($novoEventoRequest, $eventosDoRequest, $comando, $usuarioSistema);

            throw new Exception("Já existe um colaborador com o e-mail informado.");
        }

        $parametrosNovoColaborador = new EntradaFronteiraNovoColaborador(
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            colaboradorCodigo: $usuarioSistema->codigo->get(),
            nomeCompleto: $usuarioSistema->nomeCompleto->get(),
            email: $usuarioSistema->email->get(),
            oab: $usuarioSistema->oab->get(),
        );

        try {
            $this->repositorioEmpresa->novoColaborador($parametrosNovoColaborador);
        }catch(Exception $erro){

            $novoEventoRequest = new Evento("Erro ao cadastrar colaborador. (E-mail: $email, Empresa: $empresaCodigo, Nome: $nomeCompleto) - {$erro->getMessage()}");
            $this->salvaEventosDoRequest($novoEventoRequest, $eventosDoRequest, $comando, $usuarioSistema);
            throw new Exception("Erro ao cadastrar colaborador. - {$erro->getMessage()}");
        }

        $novoEventoRequest = new Evento("Colaborador cadastrado com sucesso. (E-mail: $email, Empresa: $empresaCodigo, Nome: $nomeCompleto)");
        $eventosDoRequest->adicionar($novoEventoRequest);

        $linkParaCadastrarSenha = "{$this->ambiente->get('APP_DOMINIO')}/cadastrar-senha?token={$usuarioSistema->codigo->get()}";
        $assuntoEmail = "Iniciando sua Jornada - Bem-vindo(a) à {$entidadeEmpresarial->apelido->get()}!";
        $mensagemEmail = <<<htmlMensagemEmail
        Olá {$usuarioSistema->nomeCompleto->get()},
        <br />
        <p>É com grande alegria que recebemos você em nossa plataforma {$this->jusiziEntity->fantasia}.</p>
        <br />
        <p>Para começar, sugerimos conferir nosso guia do usuário, que irá ajudá-lo a navegar em nosso software e aproveitar ao máximo nossos recursos.</p>
        <p>Fique à vontade para nos contatar caso precise de suporte em qualquer momento.</p>
        <br />
        <p>Você precisa configurar sua senha de acesso a plataforma, <a href="$linkParaCadastrarSenha" target="_blank">clique aqui</a> e configure sua senha.</p>
        <br />
        <p>Cumprimentos,</p>
        <br />
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

        $novoEventoRequest = new Evento("E-mail de boas-vindas enviado com sucesso. (E-mail ID: {$emailCodigo->emailCodigo} - Destinatário e-mail: {$usuarioSistema->email->get()}, Destinatário nome: {$usuarioSistema->nomeCompleto->get()}) (Empresa: {$usuarioSistema->empresaCodigo->get()} ID: {$usuarioSistema->codigo->get()}");
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
            accessToken: $this->accessToken->get()
        );

        $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
        
        return null;
    }

    private function salvaEventosDoRequest(Evento $novoEventoRequest, EventosDoRequest &$eventosDoRequest, Comando $comando, UsuarioSistema $usuarioSistema): void
    {
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
            accessToken: $this->accessToken->get()
        );
        $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
    }
}