<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Enumerados;

enum Grau: int
{
    case Primeiro = 1;
    case Segundo = 2;
    case Terceiro = 3;

    public function porExtenso(): string
    {
        return match ($this) {
            self::Primeiro => 'Primeiro Grau',
            self::Segundo => 'Segundo Grau',
            self::Terceiro => 'Terceiro Grau',
        };
    }
}