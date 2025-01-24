<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\ContaBancaria\Enumerados;

enum Banco: string
{
    case ASAAS = 'Asaas';
    case Nenhum = 'Nenhum';
}