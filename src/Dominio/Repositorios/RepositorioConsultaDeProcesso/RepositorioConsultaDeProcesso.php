<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\RepositorioConsultaDeProcesso;

use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarRequestPorOAB;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteirasAtualizaORequestPorOABResponseERequest;

interface RepositorioConsultaDeProcesso
{
    public function atualizaORequestPorOABResponseERequest(EntradaFronteirasAtualizaORequestPorOABResponseERequest $params): void;
    public function atualizaORequestPorOABParaFinalizado(string $requestID): void;
    public function copiarProcessosDeOABJaConsultada(string $oab, string $empresaCodigo): void;

    public function copiarProcessosDoDocumentoJaConsultada(string $documento, string $empresaCodigo): void;

    public function salvarRequestPorOAB(EntradaFronteiraSalvarRequestPorOAB $params): void;
    public function atualizarUltimaMovimentacaoDoProcesso(string $processoCodigo, string $empresaCodigo, string $ultimaMovimentacaoDescricao, string $ultimaMovimentacaoData): void;
    public function OABJaFoiConsultadaNosUltimosDias(string $oab): bool;
    public function documentoJaFoiConsultadaNosUltimosDias(string $documento): bool;

    public function salvarProcesso(EntradaFronteiraSalvarProcesso $parametros): void;
    public function salvaEvento(string $requestID, string $empresaCodigo, string $evento): void;
    public function jaExisteUmProcessoComEsteCNJ(string $processoCNJ, string $empresaCodigo): bool;
    public function adicionarProcessoParaMonitoramento(string $processoMonitoramentoCodigo, string $processoCNJ, string $empresaCodigo): void;
}