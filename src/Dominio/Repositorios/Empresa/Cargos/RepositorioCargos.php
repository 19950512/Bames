<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Empresa\Cargos;

use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\EntradaFronteiraCriarCargo;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\SaidaFronteiraBuscarCargoPorCodigo;

interface RepositorioCargos
{
    public function criarCargo(EntradaFronteiraCriarCargo $parametrosCargo): void;
    public function existeOutroCargoComEsseNome(string $nome, string $empresaCodigo): bool;
    public function buscarCargoPorCodigo(string $cargoCodigo): SaidaFronteiraBuscarCargoPorCodigo;

    public function adicionarCargoAoUsuario(string $usuarioCodigo, string $cargoCodigo, string $empresaCodigo): void;
}

