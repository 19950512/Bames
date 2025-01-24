<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class SaidaFronteiraProcessos
{

    private array $processos = [];

    public function __construct(){}

    public function add(ProcessoListagem $processo): void
    {
        $this->processos[] = $processo;
    }

    public function toArray(): array
    {
        return array_map(fn(ProcessoListagem $processo) => $processo->obterArray(), $this->processos);
    }
}
