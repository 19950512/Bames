<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class EntradaFronteiraEmitirCobranca
{
    public function __construct(
        public float $valor,
    ) {}
}
