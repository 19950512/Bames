<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes;

use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Envolvidos;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\URL;
use DateTime;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Tribunal\EntidadeTribunal;
use App\Dominio\Entidades\Processo\Fontes\Enumerados\Grau;
use App\Dominio\Entidades\Processo\Fontes\Enumerados\Tipo;

class EntidadeFonte
{

    public function __construct(
        public IdentificacaoUnica $codigo,
        public TextoSimples $id,
        public TextoSimples $descricao,
        public TextoSimples $nome,
        public TextoSimples $sigla,
        public Tipo $tipo,
        public DateTime $dataInicio,
        public DateTime $dataUltimaMovimentacao,
        public bool $segredoJustica,
        public bool $arquivado,
        public bool $fisico,
        public TextoSimples $sistema,
        public Grau $grau,
        public EntidadeCapa $capaEntity,
        public URL $url,
        public EntidadeTribunal $tribunalEntity,
        public int $quantidadeMovimentacoes,
        public int $quantidadeEnvolvidos,
        public DateTime $dataUltimaVerificacao,
        public Envolvidos $envolvidos
    ){}
    
}