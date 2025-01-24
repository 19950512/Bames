<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Envolvidos;

class Envolvidos
{
    private array $envolvidos = [];

    public function add(EntidadeEnvolvido $envolvido): void
    {
        $this->envolvidos[] = $envolvido;
    }

    public function get(): array
    {
        return $this->envolvidos;
    }
}