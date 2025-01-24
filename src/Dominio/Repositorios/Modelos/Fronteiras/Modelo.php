<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Modelos\Fronteiras;

readonly final class Modelo
{
    public function __construct(
        public string $codigo,
        public string $nome,
        public string $nomeArquivo,
    ){}

    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'nomeArquivo' => $this->nomeArquivo,
        ];
    }
}
