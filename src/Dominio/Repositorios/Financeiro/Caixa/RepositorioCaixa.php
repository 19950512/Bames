<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Financeiro\Caixa;

use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\FronteiraEntradaLancarMovimentacaoNoCaixa;
use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\FronteiraSaidaMovimentacoes;

interface RepositorioCaixa
{
    public function lancarMovimentacaoNoCaixa(FronteiraEntradaLancarMovimentacaoNoCaixa $parametros): void;

    public function obterTodasAsMovimentacoesDaConta(string $empresaCodigo, string $contaBancariaCodigo): FronteiraSaidaMovimentacoes;
    public function salvarEvento(string $contaBancariaCodigo, string $movimentacaoCodigo, string $descricao, string $empresaCodigo): void;
}