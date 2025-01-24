<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Modelos;

use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;

final readonly class LeituraModelos
{
    public function __construct(
        private RepositorioModelos $repositorioModelos,
        private EntidadeEmpresarial $entidadeEmpresarial
    ){}

    public function executar(): array
    {
        return $this->repositorioModelos->obterTodosOsModelos(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
        )->toArray();
    }
}