<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

final class SaidaObterTodosOsBoletosDoParcelamento
{
    private array $boletos = [];

    public function adicionarBoleto(BoletoParcelamento $boleto): void
    {
        $this->boletos[] = $boleto;
    }

    public function getBoletos(): array
    {
        return $this->boletos;
    }
}