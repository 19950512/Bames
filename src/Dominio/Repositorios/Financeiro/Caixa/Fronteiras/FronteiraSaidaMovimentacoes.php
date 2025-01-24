<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras;

final class FronteiraSaidaMovimentacoes
{

    private array $movimentacoes = [];
    public function adicionarMovimentacao(Movimentacao $movimentacao): void
    {
        $this->movimentacoes[] = $movimentacao;
    }

    public function obterMovimentacoes(): array
    {
        return $this->movimentacoes;
    }
}