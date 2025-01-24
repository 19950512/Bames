<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Email;

use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;

interface RepositorioEmail
{
    public function salvarEmailEnviado(FronteiraEntradaSalvarEmailEnviado $params): void;
}