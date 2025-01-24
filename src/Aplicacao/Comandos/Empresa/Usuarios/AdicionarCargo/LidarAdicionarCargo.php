<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Usuarios\AdicionarCargo;

use App\Dominio\ObjetoValor\AccessToken;
use Override;
use Exception;
use App\Dominio\Entidades\Cargo;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Empresa\Cargos\RepositorioCargos;
use App\Aplicacao\Comandos\Empresa\Usuarios\AdicionarCargo\ComandoAdicionarCargo;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

final readonly class LidarAdicionarCargo implements Lidar
{
	public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioRequest $repositorioRequest,
        private RepositorioCargos $repositorioCargos,
        private AccessToken $accessToken
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoAdicionarCargo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();
        $usuarioCodigo = $comando->obterUsuarioCodigo();
        $cargoCodigo = $comando->obterCargoCodigo();

        try {
            $parametrosEmpresa = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($parametrosEmpresa);
        }catch(Exception $erro){
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {
            $usuarioData = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $usuarioSistema = UsuarioSistema::build($usuarioData);
        }catch(Exception $erro){
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: $this->accessToken
        );

        if(!$usuarioSistema->diretorGeral){

            $novoEventoRequest = new Evento("Apenas o diretor geral pode adicionar cargo aos usuários.");
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

            throw new Exception("Apenas o diretor geral pode adicionar cargo aos usuários.");
        }

        try {
            $cargoData = $this->repositorioCargos->buscarCargoPorCodigo($cargoCodigo);
            $cargo = Cargo::build($cargoData);
        }catch(Exception $erro){
            throw new Exception("Cargo não encontrado. - {$erro->getMessage()}");
        }

        if($usuarioSistema->possuiCargo($cargo)){

            $novoEventoRequest = new Evento("Usuário já possui o cargo {$cargo->nome->get()}.");
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

            throw new Exception("Usuário já possui o cargo {$cargo->nome->get()}.");
        }

        $usuarioSistema->adicionarCargo($cargo);

        $this->repositorioCargos->adicionarCargoAoUsuario(
            usuarioCodigo: $usuarioSistema->codigo->get(),
            cargoCodigo: $cargo->codigo->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get()
        );

        $novoEventoRequest = new Evento("Cargo {$cargo->nome->get()} adicionado ao usuário com sucesso");
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
