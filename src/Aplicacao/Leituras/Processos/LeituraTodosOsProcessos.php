<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Processos;

use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

final readonly class LeituraTodosOsProcessos
{
    public function __construct(
        private RepositorioProcessos $repositorioProcessos,
        private EntidadeEmpresarial $entidadeEmpresarial
    ){}

    public function executar(): array
    {
        return $this->repositorioProcessos->getTodosProcessos(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
        )->toArray();
    }
}