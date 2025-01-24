<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Enumerados;

enum CobrancaSituacao: string
{
    case DESCONHECIDO = 'desconhecido';
    case AGUARDANDO_PAGAMENTO = 'aguardando_pagamento';
    case PAGO = 'pago';
    case CANCELADO = 'cancelado';
}