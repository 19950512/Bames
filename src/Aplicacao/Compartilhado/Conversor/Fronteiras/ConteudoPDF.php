<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Conversor\Fronteiras;

readonly final class ConteudoPDF
{
    public function __construct(
        public string $conteudo,
    ){}
}
