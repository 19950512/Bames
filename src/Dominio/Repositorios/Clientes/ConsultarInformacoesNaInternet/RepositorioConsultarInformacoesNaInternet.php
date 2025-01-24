<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet;

use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\EntradaFronteiraSalvarRequestPorDocumento;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarCPFRepositorio;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarProcessosCPFRepositorio;

interface RepositorioConsultarInformacoesNaInternet
{
    public function cobrarCustoParaConsultarDocumento(string $documento, float $custo): void;
    public function documentoJaFoiConsultadoNosUltimosDias(string $documento): bool;
    public function buscarInformacoesDoDocumento(string $documento): SaidaFronteiraConsultarCPFRepositorio;
    public function buscarProcessosDoDocumento(string $documento): SaidaFronteiraConsultarProcessosCPFRepositorio;
    public function salvarRequestPorDocumento(EntradaFronteiraSalvarRequestPorDocumento $parametros): void;
    public function consultar(string $documento): void;
}
