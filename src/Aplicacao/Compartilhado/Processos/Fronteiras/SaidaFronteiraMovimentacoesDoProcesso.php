<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Processos\Fronteiras;

final class SaidaFronteiraMovimentacoesDoProcesso
{
    private array $movimentacoes;
    public function __construct(){}

    public function adicionarMovimentacao(Movimentacao $movimentacao): void
    {
        $this->movimentacoes[] = $movimentacao;
    }

    public function obterMovimentacoes(): array
    {
        return $this->movimentacoes;
    }
}
