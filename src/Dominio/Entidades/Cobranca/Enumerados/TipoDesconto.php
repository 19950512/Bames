<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca\Enumerados;

enum TipoDesconto: string
{
    case VALOR = 'VALOR';
    case PERCENTUAL = 'PERCENTUAL';
}
