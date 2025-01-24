<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Empresa;

use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Empresa\Fronteiras\EntradaFronteiraNovoColaborador;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraBuscarUsuarioPorCodigo;

interface RepositorioEmpresa
{
    public function novoColaborador(EntradaFronteiraNovoColaborador $params): void;
    public function buscarEmpresaPorCodigo(string $empresaCodigo): SaidaFronteiraEmpresa;

    public function buscarUsuarioPorCodigo(string $contaCodigo): SaidaFronteiraBuscarUsuarioPorCodigo;
    public function jaExisteUmUsuarioComEsseEmail(string $email): bool;

    public function buscarTodosUsuarios(string $empresaCodigo): array;
    
    public function deletarTudoRelacionadoAEmpresa(string $empresaCodigo): void;

    public function totalClientes(): int;
    public function totalClientesDetalhado(): array;
}