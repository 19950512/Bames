<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Request;

use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

interface RepositorioRequest
{
    public function salvarEventosDoRequest(EntradaFronteiraSalvarEventosDoRequest $eventosDoRequest): void;
}