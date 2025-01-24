<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Empresa\Cargos;

use PDO;
use App\Dominio\Repositorios\Empresa\Cargos\RepositorioCargos;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\EntradaFronteiraCriarCargo;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\SaidaFronteiraBuscarCargoPorCodigo;

class ImplementacaoRepositorioCargos implements RepositorioCargos
{

    public function __construct(
        private PDO $pdo,
    ){}
    
    public function criarCargo(EntradaFronteiraCriarCargo $parametrosCargo): void
    {

        
    }

    public function buscarCargoPorCodigo(string $cargoCodigo): SaidaFronteiraBuscarCargoPorCodigo
    {

    }

    public function existeOutroCargoComEsseNome(string $nome, string $empresaCodigo): bool
    {
        return false;
    }

    public function adicionarCargoAoUsuario(string $usuarioCodigo, string $cargoCodigo, string $empresaCodigo): void
    {}
}