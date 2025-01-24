<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados;

enum Polo: string
{
    case Ativo = 'Ativo';
    case Passivo = 'Passivo';
}