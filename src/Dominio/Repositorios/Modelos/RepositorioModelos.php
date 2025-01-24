<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Modelos;

use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraModelo;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraTodosModelos;

interface RepositorioModelos
{
    public function criarNovoModelo(string $modeloCodigo, string $nome, string $empresaCodigo): void;
    public function excluirModelo(string $modeloCodigo, string $empresaCodigo): void;
    public function atualizarModelo(string $modeloCodigo, string $nome, string $empresaCodigo): void;
    public function obterModeloPorCodigo(string $modeloCodigo, string $empresaCodigo): SaidaFronteiraModelo;

    public function obterTodosOsModelos(string $empresaCodigo): SaidaFronteiraTodosModelos;

    public function salvarEvento(string $modeloCodigo, string $empresaCodigo, string $evento): void;

    public function vincularArquivoAoModelo(string $modeloCodigo, string $empresaCodigo, string $arquivoNome): void;
}
