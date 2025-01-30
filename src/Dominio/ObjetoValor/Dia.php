<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

readonly final class Dia
{
    public function __construct(
        private int $dia
    ){
        if($dia < 1 || $dia > 31){
            throw new Exception('Dia inválido');
        }
    }

    public function get(): int
    {
        return $this->dia;
    }
}