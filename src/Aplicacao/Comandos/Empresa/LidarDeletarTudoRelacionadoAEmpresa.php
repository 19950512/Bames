<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa;

use App\Dominio\ObjetoValor\AccessToken;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\Entidades\JusiziEntity;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

final readonly class LidarDeletarTudoRelacionadoAEmpresa implements Lidar
{
	public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioRequest $repositorioRequest,
        private RepositorioEmail $repositorioEmail,
        private Email $email,
        private JusiziEntity $jusiziEntity,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private AccessToken $accessToken,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoDeletarTudoRelacionadoAEmpresa::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeEmpresarial->codigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        if(!$this->entidadeEmpresarial->acessoTotalAutorizadoPorMatheusMaydana){

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou deletar tudo relacionado a empresa ({$this->entidadeEmpresarial->apelido->get()}, ID: {$this->entidadeEmpresarial->codigo->get()}) mas sem permissão.");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeEmpresarial->codigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
            throw new Exception("Você não tem permissão para deletar tudo relacionado a uma empresa.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();

        if($empresaCodigo == $this->entidadeEmpresarial->codigo->get()){
            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou deletar tudo relacionado a empresa ({$this->entidadeEmpresarial->apelido->get()}, ID: {$this->entidadeEmpresarial->codigo->get()}) mas não pode deletar a empresa que está logado.");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeEmpresarial->codigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
            throw new Exception("Você não pode deletar a empresa que está logado.");
        }

        try {
            $parametrosEmpresa = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarialAlvo = EntidadeEmpresarial::instanciarEntidadeEmpresarial($parametrosEmpresa);
        }catch(Exception $erro){
            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou deletar tudo relacionado a empresa ({$this->entidadeEmpresarial->apelido->get()}, ID: {$this->entidadeEmpresarial->codigo->get()}) mas .. {$erro->getMessage()}.");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeEmpresarial->codigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $this->repositorioEmpresa->deletarTudoRelacionadoAEmpresa($entidadeEmpresarialAlvo->codigo->get());
            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, deletou tudo relacionado a empresa ({$entidadeEmpresarialAlvo->apelido->get()}, ID: {$entidadeEmpresarialAlvo->codigo->get()}).");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $assuntoEmail = "Empresa deletada com sucesso - {$entidadeEmpresarialAlvo->apelido->get()}";
            $mensagemEmail = <<<htmlMensagemEmail
            Olá {$this->jusiziEntity->responsavelNome},
            <br />
            <p>Informamos que a empresa {$entidadeEmpresarialAlvo->apelido->get()} foi deletada com sucesso do sistema.</p>
            <br />
            <p>Cumprimentos,</p>
            <br />
            {$this->jusiziEntity->responsavelNome}<br/>
            {$this->jusiziEntity->responsavelCargo}<br/>
            {$this->jusiziEntity->fantasia}<br/>
            htmlMensagemEmail;

            $parametrosEnviarEmail = new EntradaFronteiraEnviarEmail(
                destinatarioEmail: $this->jusiziEntity->emailComercial,
                destinatarioNome: $this->jusiziEntity->responsavelNome,
                assunto: $assuntoEmail,
                mensagem: $mensagemEmail
            );

            $emailCodigo = $this->email->enviar($parametrosEnviarEmail);

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, enviou e-mail para {$this->jusiziEntity->responsavelNome} ({$this->jusiziEntity->emailComercial}) informando que a empresa {$entidadeEmpresarialAlvo->apelido->get()} foi deletada com sucesso.");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosEmailEnviado = new FronteiraEntradaSalvarEmailEnviado(
                emailCodigo: $emailCodigo->emailCodigo,
                assunto: $assuntoEmail,
                mensagem: "A empresa {$entidadeEmpresarialAlvo->apelido->get()} foi deletada com sucesso do sistema pelo usuário {$this->entidadeUsuarioLogado->nomeCompleto->get()}.",
                destinatarioNome: $this->jusiziEntity->responsavelNome,
                destinatarioEmail: $this->jusiziEntity->emailComercial,
                empresaID: $this->entidadeEmpresarial->codigo->get(),
                situacao: 'Enviado'
            );

            $this->repositorioEmail->salvarEmailEnviado($parametrosEmailEnviado);

        }catch(Exception $erro){
            
            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou deletar tudo relacionado a empresa ({$entidadeEmpresarialAlvo->apelido->get()}, ID: {$entidadeEmpresarialAlvo->codigo->get()}) mas .. {$erro->getMessage()}.");
            $eventosDoRequest->adicionar($novoEventoRequest);
            throw new Exception("Erro ao deletar tudo relacionado a empresa. - {$erro->getMessage()}");

        }finally {

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeEmpresarial->codigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
            
            return null;
        }

    }
}