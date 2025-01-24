<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Modelos\Fronteiras;

final class SaidaFronteiraTodosModelos
{

    private array $modelos = [];
    public function __construct(){}

    public function adicionarModelo(Modelo $modelo): void
    {
        $this->modelos[] = $modelo;
    }

    public function obterModelos(): array
    {
        return $this->modelos;
    }

    public function toArray(): array
    {
        $modelos = [];
        foreach ($this->modelos as $modelo) {
            $modelos[] = $modelo->toArray();
        }
        return $modelos;
    }
}