<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados;

enum Natureza: string
{
    case Fisica = 'Fisica';
    case Juridica = 'Juridica';
}