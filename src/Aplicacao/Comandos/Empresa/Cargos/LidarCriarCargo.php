<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Cargos;


use App\Dominio\ObjetoValor\AccessToken;
use Override;
use Exception;
use App\Dominio\Entidades\Cargo;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Comandos\Empresa\Cargos\ComandoCriarCargo;
use App\Dominio\Repositorios\Empresa\Cargos\RepositorioCargos;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\EntradaFronteiraCriarCargo;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\SaidaFronteiraBuscarCargoPorCodigo;


final readonly class LidarCriarCargo implements Lidar
{
	public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioCargos $repositorioCargos,
        private RepositorioRequest $repositorioRequest,
        private AccessToken $accessToken
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
        
        if (!is_a($comando, ComandoCriarCargo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $nome = $comando->obterNome();
        $empresaCodigo = $comando->obterEmpresaCodigo();
        $usuarioCodigo = $comando->obterUsuarioCodigo();

        $cargoCodigo = new IdentificacaoUnica();

        try {
            $parametrosEmpresa = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($parametrosEmpresa);
        }catch(Exception $erro){
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        $usuarioData = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
        $usuarioSistema = UsuarioSistema::build($usuarioData);

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $usuarioSistema->empresaCodigo,
            usuarioCodigo: $usuarioSistema->codigo,
            accessToken: $this->accessToken
        );

        $cargo = Cargo::build(
            new SaidaFronteiraBuscarCargoPorCodigo(
                cargoCodigo: $cargoCodigo->get(),
                nome: $nome,
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                descricao: ''
            )
        );

        if(!$usuarioSistema->diretorGeral){

            $novoEventoRequest = new Evento("Tentativa de criar cargo sem permissão. - {$usuarioSistema->nomeCompleto->get()} tentou criar o cargo {$cargo->nome->get()}.");
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
            throw new Exception("Apenas o diretor geral pode criar cargos.");
        }

        if($this->repositorioCargos->existeOutroCargoComEsseNome($cargo->nome->get(), $entidadeEmpresarial->codigo->get())){

            $novoEventoRequest = new Evento("Tentativa de criar cargo com nome já existente. - {$usuarioSistema->nomeCompleto->get()} tentou criar o cargo {$cargo->nome->get()}.");
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
            throw new Exception("Já existe um cargo com esse nome.");
        }

        $parametrosEntradaCargoRepositorio = new EntradaFronteiraCriarCargo(
            cargoCodigo: $cargoCodigo->get(),
            nome: $cargo->nome->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get()
        );
        $this->repositorioCargos->criarCargo($parametrosEntradaCargoRepositorio);

        $novoEventoRequest = new Evento("Cargo criado com sucesso. - {$usuarioSistema->nomeCompleto->get()} criou o cargo {$cargo->nome->get()}.");
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