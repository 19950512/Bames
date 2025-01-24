<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo\Fontes;

use App\Dominio\ObjetoValor\TextoSimples;
use DateTime;
use App\Dominio\ObjetoValor\Valor;

class EntidadeCapa
{
    public function __construct(
        public TextoSimples $classe,
        public TextoSimples $assunto,
        public TextoSimples $assuntoNormalizado,
        public TextoSimples $area,
        public TextoSimples $orgaoJulgador,
        public Valor $causaValor,
        public TextoSimples $causaMoeda,
        public DateTime $dataDistribuicao,
        public DateTime $dataArquivamento,
        public array $informacoesComplementares,
    ){}
}