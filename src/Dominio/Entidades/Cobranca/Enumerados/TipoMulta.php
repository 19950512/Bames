<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca\Enumerados;

enum TipoMulta: string
{
    case VALOR = 'VALOR';
    case PERCENTUAL = 'PERCENTUAL';
}
