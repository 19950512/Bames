<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\NovoModelo;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Modelo\EntidadeModelo;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\ArquivoTemporario;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraModelo;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;

final readonly class LidarNovoModelo implements Lidar
{

    public function __construct(
        private RepositorioRequest $repositorioRequest,
        private GerenciadorDeArquivos $gerenciadorDeArquivos,
        private RepositorioModelos $repositorioModelos,
        private RepositorioEmpresa $repositorioEmpresa,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoNovoModelo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigo());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        $usuarioCodigo = $comando->obterUsuarioCodigoPronto();

        try {

            $usuarioDados = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $entidadeUsuario = UsuarioSistema::build($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }


        $modeloCodigo = new IdentificacaoUnica();

        $arquivoCodigo = new IdentificacaoUnica();

        $arquivo = $comando->obterArquivos()->get()[0] ?? null;

        $nomeArquivo = $arquivoCodigo->get() . '.' . $arquivo->extensao;

        $entidadeModelo = EntidadeModelo::instanciarEntidadeModelo(new SaidaFronteiraModelo(
            modeloCodigo: $modeloCodigo->get(),
            nome: $comando->obterNomeModelo(),
            nomeArquivo: $nomeArquivo
        ));

        try {

            $this->repositorioModelos->criarNovoModelo(
                modeloCodigo: $entidadeModelo->codigo->get(),
                nome: $entidadeModelo->nome->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
            );

            $this->repositorioModelos->salvarEvento(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                evento: "{$entidadeUsuario->nomeCompleto->get()}, criou o modelo de documento com sucesso."
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Novo modelo criado com sucesso. - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()}"
            );

        } catch (Exception $erro){

            $this->repositorioModelos->salvarEvento(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                evento: "Erro ao criar novo modelo. - {$erro->getMessage()}"
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Erro ao criar novo modelo. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao criar novo modelo. - {$erro->getMessage()}");
        }

        try {

            $parametrosSalvarArquivo = new EntradaFronteiraSalvarArquivo(
                diretorioENomeArquivo: '/modelos/' . $nomeArquivo,
                conteudo: file_get_contents($arquivo->tmpName),
                empresaCodigo: $comando->obterEmpresaCodigo(),
            );

            $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivo);

            $this->repositorioModelos->vincularArquivoAoModelo(
                modeloCodigo: $entidadeModelo->codigo->get(),
                empresaCodigo: $comando->obterEmpresaCodigo(),
                arquivoNome: $nomeArquivo,
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ModelosDocumento,
                mensagem: "Arquivo do modelo salvo com sucesso. - {$entidadeModelo->nome->get()} para {$entidadeEmpresarial->apelido->get()}"
            );

        }catch (Exception $erro){

            throw new Exception("Erro ao salvar o arquivo do modelo. - {$erro->getMessage()}");
        }

        return null;
    }
}
