<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Processo\ProcessoEntity;
use App\Dominio\Repositorios\Processos\Fronteiras\MovimentacaoData;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;
use Exception;

final class LidarConsultarMovimentacoes implements Lidar
{
    public function __construct(
        private EntidadeEmpresarial $entidadeEmpresarial,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private ConsultaDeProcesso $consultaDeProcesso,
        private RepositorioProcessos $repositorioProcessos,
        private Cache $cache,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoLidarConsultarMovimentacoes::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $this->entidadeEmpresarial->codigo->get();
        $usuarioCodigo = $this->entidadeUsuarioLogado->codigo->get();

        try {

            $processoData = $this->repositorioProcessos->obterProcessoPorCNJ(
                CNJ: $comando->obterCNJ(),
                empresaCodigo: $empresaCodigo,
            );

            $entidadeProcesso = ProcessoEntity::instanciarEntidadeProcesso($processoData);
        }catch (Exception $erro) {
            throw new Exception("Erro ao obter processo. - {$erro->getMessage()}");
        }

        try {

            $movimentacoes = $this->consultaDeProcesso->obterMovimentacoesDoProcesso(
                CNJ: $comando->obterCNJ(),
            );

        }catch(Exception $erro){
            throw new Exception("Erro ao consultar movimentações do processo. - {$erro->getMessage()}");
        }

        $this->repositorioProcessos->atualizarTotalMovimentacoesDoProcesso(
            processoCodigo: $entidadeProcesso->codigo->get(),
            totalMovimentacoes: count($movimentacoes->obterMovimentacoes()),
        );

        $this->cache->delete(
            pattern: $this->entidadeEmpresarial->codigo->get() . '_cliente_processos*'
        );

        $dataUltimaMovimentacao = '';
        foreach($movimentacoes->obterMovimentacoes() as $movimentacao){

            $movimentacaoData = new MovimentacaoData(
                id: $movimentacao->id,
                empresaCodigo: $empresaCodigo,
                processoCodigo: $entidadeProcesso->codigo->get(),
                processoCNJ: $comando->obterCNJ(),
                data: $movimentacao->data,
                tipo: $movimentacao->tipo,
                tipoPublicacao: $movimentacao->tipoPublicacao,
                classificacaoPreditaNome: $movimentacao->classificacaoPreditaNome,
                classificacaoPreditaDescricao: $movimentacao->classificacaoPreditaDescricao,
                classificacaoPreditaHierarquia: $movimentacao->classificacaoPreditaHierarquia,
                conteudo: $movimentacao->conteudo,
                textoCategoria: $movimentacao->textoCategoria,
                fonteProcessoFonteId: $movimentacao->fonteProcessoFonteId,
                fonteFonteId: $movimentacao->fonteFonteId,
                fonteNome: $movimentacao->fonteNome,
                fonteTipo: $movimentacao->fonteTipo,
                fonteSigla: $movimentacao->fonteSigla,
                fonteGrau: $movimentacao->fonteGrau,
                fonteGrauFormatado: $movimentacao->fonteGrauFormatado,
            );

            if(empty($dataUltimaMovimentacao)){
                $dataUltimaMovimentacao = (string) $movimentacao->data;
            }

            if(date('Y-m-d', strtotime($movimentacao->data)) >= date('Y-m-d', strtotime($dataUltimaMovimentacao))){
                $dataUltimaMovimentacao = date('Y-m-d', strtotime($movimentacao->data));
            }

            if($this->repositorioProcessos->movimentacaoNaoExisteAinda(
                codigoMovimentacaoNaPlataforma: (string) $movimentacao->id,
                empresaCodigo: $empresaCodigo
            )){

                $this->repositorioProcessos->salvarMovimentacaoDoProcesso(
                    parametros: $movimentacaoData,
                );

            }else{

                $this->repositorioProcessos->atualizarMovimentacaoDoProcesso(
                    parametros: $movimentacaoData,
                );
            }
        }

        if(!empty($dataUltimaMovimentacao)){
            $this->repositorioProcessos->atualizarDataUltimaMovimentacaoDoProcesso(
                processoCodigo: $entidadeProcesso->codigo->get(),
                dataUltimaMovimentacao: $dataUltimaMovimentacao,
            );
        }

        return null;
    }
}
