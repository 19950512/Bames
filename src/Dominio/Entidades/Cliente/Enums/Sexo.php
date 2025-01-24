<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cliente\Enums;

use Exception;

enum Sexo: string
{
    case MASCULINO = 'M';
    case FEMININO = 'F';

    case NAO_INFORMADO = 'N';

    public static function get(string $sexo): Sexo
    {
        $sexo = mb_strtolower($sexo);

        return match($sexo) {
            'm','masculino','homem','ele' => self::MASCULINO,
            'f','feminino','feminina','mulher','ela' => self::FEMININO,
            default => self::NAO_INFORMADO
        };
    }

    public function getDescricao(): string
    {
        return match($this) {
            self::MASCULINO => 'Masculino',
            self::FEMININO => 'Feminino',
            self::NAO_INFORMADO => 'Não informado',
        };
    }
}
