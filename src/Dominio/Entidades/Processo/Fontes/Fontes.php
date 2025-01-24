<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes;

class Fontes
{
    private array $fontes = [];

    public function add(EntidadeFonte $fonte): void
    {
        $this->fontes[] = $fonte;
    }

    public function get(): array
    {
        return $this->fontes;
    }
}