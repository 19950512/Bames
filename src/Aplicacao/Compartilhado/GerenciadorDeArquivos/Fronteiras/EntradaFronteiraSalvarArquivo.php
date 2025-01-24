<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras;

readonly final class EntradaFronteiraSalvarArquivo
{

    public function __construct(
        public string $diretorioENomeArquivo,
        public string $conteudo,
        public string $empresaCodigo,
    ){}
}
