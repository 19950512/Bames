<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Contrato;

use App\Dominio\Repositorios\Contrato\Fronteiras\SaidaFronteiraContrato;
use App\Dominio\Repositorios\Contrato\Fronteiras\EntradaFronteiraCriarContrato;

interface RepositorioContrato
{
    public function buscarContratoPorCodigo(string $contratoCodigo, string $empresaCodigo): SaidaFronteiraContrato;
    public function criarContrato(EntradaFronteiraCriarContrato $parametros): void;
    public function salvarEvento(string $contratoCodigo, string $empresaCodigo, string $evento): void;
}