<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Boleto\Enumerados;

enum Status: string
{
    case NAO_REGISTRADO = 'Não Registrado';
    case REGISTRADO = 'Registrado';
    case PAGO = 'Pago';
    case EMITIDO_AGUARDANDO_REGISTRO = 'Emitido Aguardando Registro';
    case CANCELADO = 'Cancelado';
}
