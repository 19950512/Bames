<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\AtualizarFCMToken;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;
use Override;

final readonly class LidarAtualizarFCMToken implements Lidar
{

	public function __construct(
        private EntidadeEmpresarial $entidadeEmpresarial,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private AccessToken $accessToken,
        private RepositorioRequest $repositorioRequest,
        private RepositorioAutenticacao $repositorioAutenticacao
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoAtualizarFCMToken::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $fcmToken = $comando->obterFCMToken();

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeUsuarioLogado->empresaCodigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        if($this->repositorioAutenticacao->oFCMTokenJaEstaCadastrado(
            entidadeEmpresarial: $this->entidadeEmpresarial->codigo->get(),
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo->get(),
            FCMToken: $fcmToken,
        )){

            $novoEventoRequest = new Evento("O FCM Token ($fcmToken) informado já está cadastrado.");
            $eventosDoRequest->adicionar($novoEventoRequest);

            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeUsuarioLogado->empresaCodigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);
            return null;
        }

        $this->repositorioAutenticacao->salvarNovoFCMToken(
            entidadeEmpresarial: $this->entidadeEmpresarial->codigo->get(),
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo->get(),
            FCMToken: $fcmToken,
        );

        $novoEventoRequest = new Evento("O FCM Token ($fcmToken) foi cadastrado com sucesso.");
        $eventosDoRequest->adicionar($novoEventoRequest);

        $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
            comandoPayload: json_encode($comando->getPayload()),
            comando: $comando::class,
            usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
            businessId: $this->entidadeUsuarioLogado->empresaCodigo->get(),
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