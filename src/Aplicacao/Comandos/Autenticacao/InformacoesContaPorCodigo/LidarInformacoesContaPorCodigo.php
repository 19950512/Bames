<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\InformacoesContaPorCodigo;

use App\Dominio\ObjetoValor\AccessToken;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Aplicacao\Comandos\Autenticacao\InformacoesContaPorCodigo\ComandoInformacoesContaPorCodigo;

readonly class LidarInformacoesContaPorCodigo implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private RepositorioRequest $repositorioRequest,
    ){}

	#[Override] public function lidar(Comando $comando): UsuarioSistema
	{

		if(!is_a($comando, ComandoInformacoesContaPorCodigo::class)){
			throw new Exception("Ops, não sei lidar com esse comando.");
		}

		$contaCodigo = $comando->obterContaCodigo();

        try {

            $contaData = $this->repositorioAutenticacaoComando->buscarContaPorCodigo(
                contaCodigo: $contaCodigo,
            );

            $usuarioSistema = UsuarioSistema::build($contaData);

        }catch (Exception $erro){
            throw new Exception("E-mail ou senha inválidos.");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: new AccessToken('')
        );

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

        return $usuarioSistema;
	}
}