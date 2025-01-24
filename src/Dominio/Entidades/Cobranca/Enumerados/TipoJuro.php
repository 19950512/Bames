<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca\Enumerados;

enum TipoJuro: string
{
    case VALOR = 'VALOR';
    case PERCENTUAL = 'PERCENTUAL';
}
