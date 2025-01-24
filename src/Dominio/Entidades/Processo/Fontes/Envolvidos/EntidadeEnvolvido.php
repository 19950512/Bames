<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes\Envolvidos;

use App\Dominio\ObjetoValor\Descricao;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Polo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Tipo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Natureza;

class EntidadeEnvolvido
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public NomeCompleto $nomeCompleto,
        public int $quantidadeProcessos,
        public Natureza $tipoNatureza,
        public DocumentoIdentificacao $documento,
        public Tipo $tipo,
        public Polo $polo,
        public Descricao $sufixo = new Descricao('')
    ){}
}