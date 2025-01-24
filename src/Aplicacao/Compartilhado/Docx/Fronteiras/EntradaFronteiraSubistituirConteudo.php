<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Docx\Fronteiras;

final class EntradaFronteiraSubistituirConteudo
{
    public function __construct(
        public string $conteudoDoArquivoDocx,
        public array $subistituicoes
    ){}
}
