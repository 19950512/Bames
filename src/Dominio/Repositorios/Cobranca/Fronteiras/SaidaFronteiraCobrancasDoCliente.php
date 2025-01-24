<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Cobranca\Fronteiras;

final class SaidaFronteiraCobrancasDoCliente
{
    private array $cobrancas = [];

    public function __construct(){}

    public function adicionarCobranca(Cobranca $cobranca): void
    {
        $this->cobrancas[] = $cobranca;
    }

    public function obterCobrancas(): array
    {
        return $this->cobrancas;
    }
}
