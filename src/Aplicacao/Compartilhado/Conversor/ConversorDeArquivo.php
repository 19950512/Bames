<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Conversor;

use App\Aplicacao\Compartilhado\Conversor\Fronteiras\ConteudoPDF;

interface ConversorDeArquivo
{
    public function docxToPDF(string $conteudo, string $arquivoNome): ConteudoPDF;
}
