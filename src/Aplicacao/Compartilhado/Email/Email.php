<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Email;

use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Aplicacao\Compartilhado\Email\Fronteiras\SaidaFronteiraEmailCodigo;

interface Email
{
    public function enviar(EntradaFronteiraEnviarEmail $params): SaidaFronteiraEmailCodigo;
}