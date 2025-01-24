<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

readonly final class Evento
{
    public string $momento;
    public function __construct(
        public string $descricao
    ){
        $this->momento = date('Y-m-d H:i:s', time());
    }

    public function get(): string
    {
        return $this->descricao;
    }
}
