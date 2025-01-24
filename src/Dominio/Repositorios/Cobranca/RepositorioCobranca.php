<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Cobranca;

use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\EntradaFronteiraCriarUmaCobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\SaidaFronteiraCobrancasDoCliente;

interface RepositorioCobranca
{
    public function criarUmaCobranca(EntradaFronteiraCriarUmaCobranca $parametros): void;
    public function buscarCobrancasDoCliente(string $clienteCodigo, string $empresaCodigo): SaidaFronteiraCobrancasDoCliente;
    public function buscarTodasAsCobrancas(string $empresaCodigo): SaidaFronteiraCobrancasDoCliente;
    public function buscarCobrancaPorCodigo(string $cobrancaCodigo, string $empresaCodigo): Cobranca;
    public function buscarCobrancaPorCodigoDaPlataformaAPI(string $cobrancaCodigoPlataforma, string $empresaCodigo): Cobranca;

    public function novoEvento(string $cobrancaCodigo, string $empresaCodigo, string $descricao): void;
    public function atualizarCodigoDaCobrancaNaPlataforma(string $cobrancaCodigo, string $empresaCodigo, string $codigoDaCobrancaNaPlataformaDeCobranca): void;
}
