<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Agenda;

use DateTime;
use App\Dominio\ObjetoValor\Descricao;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraBuscarEvento;

class EntidadeEvento
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public IdentificacaoUnica $empresaCodigo,
        public IdentificacaoUnica $usuarioCodigo,
        public Descricao $titulo,
        public Descricao $descricao,
        public bool $diaTodo,
        public int $recorrencia,
        public DateTime $horarioInicio,
        public DateTime $horarioFim,
        public DateTime $criadoEm,
        public string $status,
        public string $agendaID = ''
    ) {}

    public static function build(SaidaFronteiraBuscarEvento $parametros): EntidadeEvento
    {
        return new EntidadeEvento(
            codigo: new IdentificacaoUnica($parametros->codigo),
            titulo: new Descricao($parametros->titulo),
            descricao: new Descricao($parametros->descricao),
            diaTodo: $parametros->diaTodo,
            recorrencia: $parametros->recorrencia,
            horarioInicio: new DateTime($parametros->dataInicio),
            horarioFim: new DateTime($parametros->dataFim),
            agendaID: $parametros->plataforma_id,
            criadoEm: new DateTime($parametros->momento),
            empresaCodigo: new IdentificacaoUnica($parametros->business_id),
            usuarioCodigo: new IdentificacaoUnica($parametros->usuario_id),
            status: $parametros->status
        );
    }
}
