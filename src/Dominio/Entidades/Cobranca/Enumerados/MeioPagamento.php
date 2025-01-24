<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca\Enumerados;

enum MeioPagamento: string
{
    case Dinheiro = 'Dinheiro';
    case Cartao = 'Cartao';
    case Pix = 'Pix';
    case Boleto = 'Boleto';
    case Outros = 'Outros';

    public static function obterTodos(): array
    {
        return [
            self::Dinheiro,
            self::Cartao,
            self::Pix,
            self::Boleto,
            self::Outros,
        ];
    }
}
