<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class EntradaFronteiraSincronizarMovimentacoesDoProcesso
{

    private array $movimentacoes = [];

    public function __construct(
        public string $CNJ,
        public string $empresaCodigo,
    ){}

    public function adicionarMovimentacao(MovimentacaoData $movimentacao): void
    {
        $this->movimentacoes[] = $movimentacao;
    }

    public function obterMovimentacoes(): array
    {
        return $this->movimentacoes;
    }
}
