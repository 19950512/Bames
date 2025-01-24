<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Token;

use App\Dominio\Repositorios\Token\Fronteiras\EntradaFronteiraSalvarToken;
use App\Dominio\Repositorios\Token\Fronteiras\SaidaFronteiraToken;

interface RepositorioToken
{
    public function buscarTokenPorContaBancaria(int $contaBancariaCodigo, string $nomeBanco): SaidaFronteiraToken;
    public function novoToken(EntradaFronteiraSalvarToken $params): void;
}