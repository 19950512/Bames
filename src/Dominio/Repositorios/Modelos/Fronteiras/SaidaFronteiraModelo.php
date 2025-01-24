<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Modelos\Fronteiras;

readonly final class SaidaFronteiraModelo
{
    public function __construct(
        public string $modeloCodigo,
        public string $nome,
        public string $nomeArquivo,
    ){}
}