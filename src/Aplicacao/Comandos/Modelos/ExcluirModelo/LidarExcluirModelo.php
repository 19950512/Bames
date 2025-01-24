<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\ExcluirModelo;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Modelo\EntidadeModelo;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;

final readonly class LidarExcluirModelo implements Lidar
{

    public function __construct(
        private RepositorioRequest $repositorioRequest,
        private GerenciadorDeArquivos $gerenciadorDeArquivos,
        private RepositorioModelos $repositorioModelos,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private AccessToken $accessToken,
        private RepositorioEmpresa $repositorioEmpresa,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoExcluirModelo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigo());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        $modeloDados = $this->repositorioModelos->obterModeloPorCodigo(
            modeloCodigo: $comando->obterCodigoModelo(),
            empresaCodigo: $comando->obterEmpresaCodigo()
        );

        $entidadeModelo = EntidadeModelo::instanciarEntidadeModelo($modeloDados);

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeUsuarioLogado->empresaCodigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        try {

            $this->repositorioModelos->excluirModelo(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo()
            );

            $novoEventoRequest = new Evento("Modelo {$entidadeModelo->nome->get()} foi excluído pelo usuário {$this->entidadeUsuarioLogado->nomeCompleto->get()} da empresa {$entidadeEmpresarial->apelido->get()}");
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

            $this->repositorioModelos->salvarEvento(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                evento: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, excluiu o modelo {$entidadeModelo->nome->get()}"
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, excluiu o modelo {$entidadeModelo->nome->get()} da empresa {$entidadeEmpresarial->apelido->get()}"
            );

        }catch (Exception $erro){

            $novoEventoRequest = new Evento("Erro ao atualizar o modelo. - {$erro->getMessage()}");
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

            $this->repositorioModelos->salvarEvento(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                evento: "Erro ao excluir o modelo. - {$erro->getMessage()}"
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Erro ao excluir o modelo. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao excluir o modelo. - {$erro->getMessage()}");
        }

        try {

            $this->gerenciadorDeArquivos->deletarArquivo(
                diretorioENomeArquivo: '/modelos/' . $entidadeModelo->nomeArquivo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem:"Modelo excluído com sucesso. - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()}"
            );

        }catch (Exception $erro){

            throw new Exception("Erro ao excluir o arquivo do modelo. - {$erro->getMessage()}");
        }

        return null;
    }
}
