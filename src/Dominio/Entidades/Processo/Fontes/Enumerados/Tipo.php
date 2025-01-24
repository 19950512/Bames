<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Enumerados;

enum Tipo: string
{
    case Tribunal = 'Tribunal';
    case DiarioOficial = 'Diário Oficial';
    case Outros = 'Outros';
}