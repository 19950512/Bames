<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Empresa;

use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;

final class LeituraEmpresaSubstituicoes
{
    public function __construct(
        private EntidadeEmpresarial $entidadeEmpresarial
    ){}

    public function executar(): array
    {
        return $this->entidadeEmpresarial->substituicoes();
    }
}