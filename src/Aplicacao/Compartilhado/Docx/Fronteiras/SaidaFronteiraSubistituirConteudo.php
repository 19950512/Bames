<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Docx\Fronteiras;

final class SaidaFronteiraSubistituirConteudo
{
    public function __construct(
        public string $caminho,
        public string $conteudo
    ){}
}