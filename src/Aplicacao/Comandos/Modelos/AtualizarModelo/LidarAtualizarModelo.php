<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\AtualizarModelo;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Modelo\EntidadeModelo;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;

final readonly class LidarAtualizarModelo implements Lidar
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

        if (!is_a($comando, ComandoAtualizarModelo::class)) {
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

        $arquivos = $comando->obterArquivos();

        if($comando->obterNomeModelo() != $entidadeModelo->nome->get()){

            $this->repositorioModelos->salvarEvento(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                evento: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, atualizou o nome do modelo de documento de {$entidadeModelo->nome->get()} para {$comando->obterNomeModelo()}"
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Nome do modelo atualizado com sucesso. - {$entidadeModelo->nome->get()} para {$comando->obterNomeModelo()}"
            );
        }

        try {

            $this->repositorioModelos->atualizarModelo(
                modeloCodigo: $entidadeModelo->codigo->get(),
                nome: $comando->obterNomeModelo(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
            );

            $novoEventoRequest = new Evento("Modelo atualizado - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()} pelo usuário {$this->entidadeUsuarioLogado->nomeCompleto->get()}");
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
                evento: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, atualizou o modelo de documento com sucesso."
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Modelo atualizado com sucesso. - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()}"
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
                evento: "Erro ao criar novo modelo. - {$erro->getMessage()}"
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Erro ao atualizar o modelo. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao atualizar o modelo. - {$erro->getMessage()}");
        }

        if(count($arquivos->get()) > 0){

            try {

                $this->gerenciadorDeArquivos->deletarArquivo(
                    diretorioENomeArquivo: '/modelos/' . $entidadeModelo->nomeArquivo->get(),
                    empresaCodigo: $comando->obterEmpresaCodigo(),
                );

                $arquivoCodigo = new IdentificacaoUnica();

                $arquivo = $comando->obterArquivos()->get()[0] ?? null;

                $nomeArquivo = $arquivoCodigo->get() . '.' . $arquivo->extensao;

                $parametrosSalvarArquivo = new EntradaFronteiraSalvarArquivo(
                    diretorioENomeArquivo: '/modelos/' . $nomeArquivo,
                    conteudo: file_get_contents($arquivo->tmpName),
                    empresaCodigo: $comando->obterEmpresaCodigo(),
                );

                $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivo);

                $novoEventoRequest = new Evento("Arquivo do modelo foi atualizado - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()} pelo usuário {$this->entidadeUsuarioLogado->nomeCompleto->get()}");
                $eventosDoRequest->adicionar($novoEventoRequest);

                $this->repositorioModelos->vincularArquivoAoModelo(
                    modeloCodigo: $entidadeModelo->codigo->get(),
                    empresaCodigo: $comando->obterEmpresaCodigo(),
                    arquivoNome: $nomeArquivo,
                );

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::ModelosDocumento,
                    mensagem: "Arquivo do modelo atualizado com sucesso. - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()}"
                );

            }catch (Exception $erro){

                throw new Exception("Erro ao salvar o arquivo do modelo. - {$erro->getMessage()}");
            }
        }

        return null;
    }
}
