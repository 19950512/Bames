<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Atendimento\Enums;

enum Status: string
{
    case ABERTO = 'ABERTO';
    case EM_ANDAMENTO = 'EM ANDAMENTO';
    case FINALIZADO = 'FINALIZADO';
    case CANCELADO = 'CANCELADO';
}