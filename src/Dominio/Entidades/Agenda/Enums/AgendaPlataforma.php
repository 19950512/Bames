<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Agenda\Enums;

enum AgendaPlataforma: string
{
    case NENHUMA = 'Nenhuma';
    case GOOGLE = 'Google';
    case MICROSOFT = 'Microsoft';
    case APPLE = 'Apple';
}