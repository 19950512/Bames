<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Processos;

use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraMovimentacoesDoProcesso;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraProcessosPorOAB;

interface ConsultaDeProcesso
{
    public function OAB(string $OAB): SaidaFronteiraProcessosPorOAB;

    public function numeroDocumento(string $numeroDocumento): SaidaFronteiraProcessosPorOAB;
    public function solicitarAtualizacaoDoProcesso(string $CNJ): void;

    public function obterMovimentacoesDoProcesso(string $CNJ): SaidaFronteiraMovimentacoesDoProcesso;

    public function monitorarUmProcesso(string $CNJ): true;
}