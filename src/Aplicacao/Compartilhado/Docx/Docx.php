<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Docx;

use App\Aplicacao\Compartilhado\Docx\Fronteiras\EntradaFronteiraSubistituirConteudo;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\SaidaFronteiraSubistituirConteudo;

interface Docx
{
    public function substituicaoUtil(): array;
    public function substituicaoUtilCaixaAlta(): array;

    public function substituirConteudo(EntradaFronteiraSubistituirConteudo $parametros): SaidaFronteiraSubistituirConteudo;
}