<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class ProcessoListagem
{
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

    public function obterArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'numeroCNJ' => $this->numeroCNJ,
            'dataUltimaMovimentacao' => $this->dataUltimaMovimentacao,
            'quantidadeMovimentacoes' => $this->quantidadeMovimentacoes,
            'demandante' => $this->demandante,
            'demandado' => $this->demandado,
            'ultimaMovimentacaoData' => $this->ultimaMovimentacaoData,
            'ultimaMovimentacaoDescricao' => $this->ultimaMovimentacaoDescricao
        ];
    }
}