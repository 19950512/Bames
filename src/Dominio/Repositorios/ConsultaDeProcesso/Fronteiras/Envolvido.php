<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras;

readonly final class Envolvido
{
    public function __construct(
        public string $codigo,
        public string $nomeCompleto,
        public int $quantidadeProcessos,
        public string $documento,
        public string $tipo,
        public string $polo,
        public string $oab,
    ){}
}
