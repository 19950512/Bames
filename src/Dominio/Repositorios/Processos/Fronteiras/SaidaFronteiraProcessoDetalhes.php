<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class SaidaFronteiraProcessoDetalhes
{
    private array $envolvidos = [];
    private array $movimentacoes = [];
    public function __construct(
        public string $codigo,
        public string $numeroCNJ,
        public string $dataUltimaMovimentacao,
        public int $quantidadeMovimentacoes,
        public string $demandante,
        public string $demandado,
        public string $ultimaMovimentacaoData,
        public string $ultimaMovimentacaoDescricao,
    ){}

    public function addEnvolvido(EnvolvidoData $envolvido): void
    {
        $this->envolvidos[] = $envolvido;
    }

    public function addMovimentacao(MovimentacaoData $movimentacao): void
    {
        $this->movimentacoes[] = $movimentacao;
    }

    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'numeroCNJ' => $this->numeroCNJ,
            'dataUltimaMovimentacao' => $this->dataUltimaMovimentacao,
            'quantidadeMovimentacoes' => $this->quantidadeMovimentacoes,
            'demandante' => $this->demandante,
            'demandado' => $this->demandado,
            'ultimaMovimentacaoData' => $this->ultimaMovimentacaoData,
            'ultimaMovimentacaoDescricao' => $this->ultimaMovimentacaoDescricao,
            'envolvidos' => array_map(fn(EnvolvidoData $envolvido) => $envolvido->obterArray(), $this->envolvidos),
            'movimentacoes' => array_map(fn(MovimentacaoData $movimentacao) => $movimentacao->obterArray(), $this->movimentacoes)
        ];
    }
}
