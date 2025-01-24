<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\PlanoDeContas\Fronteiras;

readonly final class SaidaFronteiraPlanoDeConta
{
    public function __construct(
        public int $planoDeContasCodigo,
        public string $planoDeContasNome,
        public string $planoDeContasDescricao,
        public string $planoDeContasTipo,
        public string $planoDeContasCategoria,
        public int $planoDeContasNivel,
        public int $paiId
    ){}

    public function toArray(): array
    {
        return [
            'planoDeContasCodigo' => $this->planoDeContasCodigo,
            'planoDeContasNome' => $this->planoDeContasNome,
            'planoDeContasDescricao' => $this->planoDeContasDescricao,
            'planoDeContasTipo' => $this->planoDeContasTipo,
            'planoDeContasCategoria' => $this->planoDeContasCategoria,
            'planoDeContasNivel' => $this->planoDeContasNivel,
            'paiId' => $this->paiId
        ];
    }
}