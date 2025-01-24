<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\ContaBancaria\Enumerados;

enum AmbienteConta: string
{
    case Sandbox = 'Sandbox';
    case Producao = 'Producao';

    public static function obterTodos(): array
    {
        return [
            self::Sandbox,
            self::Producao,
        ];
    }
}