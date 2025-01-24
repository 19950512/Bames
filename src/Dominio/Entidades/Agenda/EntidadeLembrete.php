<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Agenda;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

class EntidadeEvento
{
    public function __construct(
        public IdentificacaoUnica $codigo,
    ){}
}