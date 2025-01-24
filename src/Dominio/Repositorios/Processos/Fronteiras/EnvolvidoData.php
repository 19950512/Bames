<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class EnvolvidoData
{
    public function __construct(
        public string $codigo,
        public string $oab,
        public string $tipo,
        public string $nomeCompleto,
        public string $documento,
        public int $quantidadeDeProcessos,
    ){}

    public function obterArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'oab' => $this->oab,
            'tipo' => $this->tipo,
            'nomeCompleto' => $this->nomeCompleto,
            'documento' => $this->documento,
            'quantidadeDeProcessos' => $this->quantidadeDeProcessos,
        ];
    }
}
