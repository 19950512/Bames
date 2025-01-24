<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos;

use App\Dominio\Repositorios\Processos\Fronteiras\EntradaFronteiraSincronizarMovimentacoesDoProcesso;
use App\Dominio\Repositorios\Processos\Fronteiras\MovimentacaoData;
use App\Dominio\Repositorios\Processos\Fronteiras\ProcessoListagem;
use App\Dominio\Repositorios\Processos\Fronteiras\SaidaFronteiraProcessoDetalhes;
use App\Dominio\Repositorios\Processos\Fronteiras\SaidaFronteiraProcessos;

interface RepositorioProcessos
{
    public function obterProcessosDaOAB(string $empresaCodigo, string $oab): SaidaFronteiraProcessos;
    public function obterDetalhesDoProcesso(string $empresaCodigo, string $processoCodigo): SaidaFronteiraProcessoDetalhes;

    public function getTodosProcessos(string $empresaCodigo): SaidaFronteiraProcessos;
    public function obterProcessosDoClientePorDocumento(string $empresaCodigo, string $documento): SaidaFronteiraProcessos;

    public function obterProcessoPorCNJ(string $CNJ, string $empresaCodigo): ProcessoListagem;

    public function sincronizarMovimentacoesDoProcesso(EntradaFronteiraSincronizarMovimentacoesDoProcesso $parametros): void;

    public function movimentacaoNaoExisteAinda(string $codigoMovimentacaoNaPlataforma, string $empresaCodigo): bool;

    public function salvarMovimentacaoDoProcesso(MovimentacaoData $parametros): void;

    public function atualizarMovimentacaoDoProcesso(MovimentacaoData $parametros): void;

    public function atualizarTotalMovimentacoesDoProcesso(string $processoCodigo, int $totalMovimentacoes): void;
    public function atualizarDataUltimaMovimentacaoDoProcesso(string $processoCodigo, string $dataUltimaMovimentacao): void;
}