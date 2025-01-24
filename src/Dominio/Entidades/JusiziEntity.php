<?php

declare(strict_types=1);

namespace App\Dominio\Entidades;

readonly final class JusiziEntity
{
    public function __construct(
        public string $fantasia,
        public string $responsavelNome,
        public string $emailComercial,
        public string $responsavelCargo
    ){}
}
