<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Atendimento\Fronteiras;

final readonly class EntradaFronteiraExcluir
{
    public function __construct(
        public string $codigo,
    ){}
}