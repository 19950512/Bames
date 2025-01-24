<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Tribunal;

use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;

class EntidadeTribunal
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public TextoSimples $codigoTribunal,
        public TextoSimples $nome,
        public TextoSimples $sigla,
    ){}
}