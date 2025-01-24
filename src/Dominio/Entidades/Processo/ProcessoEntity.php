<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Processo;

use App\Dominio\Repositorios\Processos\Fronteiras\ProcessoListagem;
use DateTime;
use App\Dominio\ObjetoValor\CNJ;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Processo\Fontes\Fontes;

class ProcessoEntity
{
    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        public CNJ $cnj,
        public DateTime $dataUltimaMovimentacao,
        public int $quantidadeMovimentacoes,
        public DateTime $dataUltimaVerificacao,
        public Fontes $fontes,
    ){}

    public static function instanciarEntidadeProcesso(ProcessoListagem $processo): ProcessoEntity
    {

        $fontes = new Fontes();

        return new ProcessoEntity(
            codigo: new IdentificacaoUnica($processo->codigo),
            cnj: new CNJ($processo->numeroCNJ),
            dataUltimaMovimentacao: new DateTime(date('Y-m-d', strtotime(str_replace('/', '-', $processo->dataUltimaMovimentacao)))),
            quantidadeMovimentacoes: $processo->quantidadeMovimentacoes,
            dataUltimaVerificacao: new DateTime(),
            fontes: $fontes,
        );
    }
}