<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Financeiro;

use App\Aplicacao\Compartilhado\Data\Data;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\Movimentacao;
use App\Dominio\Repositorios\Financeiro\Caixa\RepositorioCaixa;

final class LeituraTodasAsMovimntacoesDaConta
{
    public function __construct(
        private EntidadeEmpresarial $entidadeEmpresarial,
        private RepositorioCaixa $repositorioCaixa,
        private Data $data
    ){}

    public function executar(string $contaBancariaCodigo): array
    {

        return array_map(function($movimentacao){

            if(is_a($movimentacao, Movimentacao::class)){
                return [
                    'planoDeContaCodigo' => $movimentacao->planoDeContaCodigo,
                    'planoDeContaNome' => $movimentacao->planoDeContaNome,
                    'codigoMovimentacao' => $movimentacao->codigoMovimentacao,
                    'valor' => $movimentacao->valor,
                    'pagadorNomeCompleto' => $movimentacao->pagadorNomeCompleto,
                    'pagadorCodigo' => $movimentacao->pagadorCodigo,
                    'pagadorDocumento' => $movimentacao->pagadorDocumento,
                    'descricao' => $movimentacao->descricao,
                    'dataMovimentacao' => $movimentacao->dataMovimentacao,
                    'dataMovimentacaoAt' => date('d', strtotime($movimentacao->dataMovimentacao)).' '.mb_strtoupper($this->data->mesAbreviado((int) date('m', strtotime($movimentacao->dataMovimentacao)))),
                    'boletoCodigo' => $movimentacao->boletoCodigo,
                    'cobrancaCodigo' => $movimentacao->cobrancaCodigo,
                ];
            }

        }, $this->repositorioCaixa->obterTodasAsMovimentacoesDaConta(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
            contaBancariaCodigo: $contaBancariaCodigo,
        )->obterMovimentacoes());
    }
}