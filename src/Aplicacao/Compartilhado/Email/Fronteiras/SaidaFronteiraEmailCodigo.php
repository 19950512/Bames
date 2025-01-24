<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Email\Fronteiras;

readonly final class SaidaFronteiraEmailCodigo
{
    public function __construct(
        public string $emailCodigo
    ){}
}