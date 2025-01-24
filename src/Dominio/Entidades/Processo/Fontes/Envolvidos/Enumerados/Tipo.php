<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados;

enum Tipo: string
{
    case Apelante = 'Apelante';
    case Apelado = 'Apelado';
    case Relator = 'Relator';
}