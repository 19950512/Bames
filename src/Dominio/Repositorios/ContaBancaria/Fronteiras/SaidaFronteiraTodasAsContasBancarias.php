<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ContaBancaria\Fronteiras;

final class SaidaFronteiraTodasAsContasBancarias
{
    private array $contasBancarias = [];

    public function adicionarContaBancaria(ContaBancaria $contaBancaria): void
    {
        $this->contasBancarias[] = $contaBancaria;
    }

    public function obterContasBancarias(): array
    {
        return $this->contasBancarias;
    }
}

