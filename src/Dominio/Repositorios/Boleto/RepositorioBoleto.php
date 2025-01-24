<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Boleto;

use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraCriarBoleto;
use App\Dominio\Repositorios\Boleto\Fronteiras\SaidaFronteiraBoleto;

interface RepositorioBoleto
{

    public function boletoFoiAceitoPelaPlataforma(string $empresaCodigo, string $novoStatus, string $boletoCodigo): void;
    public function boletoFoiPagoNaPlataforma(string $empresaCodigo, string $novoStatus, string $dataPagamento, string $boletoCodigo, float $valorRecebido): void;
    public function boletofoiLiquidadoManualmente(string $empresaCodigo, string $boletoQuemLiquidouManualmente, string $novoStatus, string $dataPagamento, string $boletoCodigo, float $valorRecebido): void;
    public function buscarBoletoPorCodigoNaPlataforma(string $codigoBoletoNaPlataformaAPI, string $empresaCodigo): SaidaFronteiraBoleto;
    public function buscarBoletoPorCodigo(string $codigoBoleto, string $empresaCodigo): SaidaFronteiraBoleto;
    public function criarBoleto(EntradaFronteiraCriarBoleto $parametrosEntrada): void;
    public function boletoFoiEmitidoNaPlataformaCobranca(EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca $parametrosEntrada): void;

    public function existeUmBoletoNoSistemaComEsseCodigoDePlataformaDeCobranca(string $codigoBoletoNaPlataformaAPI, string $empresaCodigo): bool;

    public function atualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI(EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI $parametros): void;
    public function boletoFoiCancelado(string $empresaCodigo, string $boletoCodigo, string $boletoStatus): void;
}
