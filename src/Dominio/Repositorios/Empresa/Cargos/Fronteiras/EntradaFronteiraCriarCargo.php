<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Empresa\Cargos\Fronteiras;

readonly final class EntradaFronteiraCriarCargo
{
    public function __construct(
        public string $cargoCodigo,
        public string $nome,
        public string $empresaCodigo
    ){}
}