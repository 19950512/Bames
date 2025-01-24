<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Atendimento;

use DateTime;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Atendimento\Enums\Status;
use App\Dominio\Repositorios\Atendimento\Fronteiras\SaidaFronteiraBuscarAtendimento;

class EntidadeAtendimento
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public IdentificacaoUnica $empresaCodigo,
        public IdentificacaoUnica $clienteCodigo,
        public Status $status,
        public TextoSimples $descricao,
        public DateTime $dataInicio,
    ){}

    public function instanciarEntidadeAtendimento(SaidaFronteiraBuscarAtendimento $parametros): EntidadeAtendimento
    {
        return new EntidadeAtendimento(
            codigo: new IdentificacaoUnica($parametros->atendimentoCodigo),
            empresaCodigo: new IdentificacaoUnica($parametros->empresaCodigo),
            clienteCodigo: new IdentificacaoUnica($parametros->clienteCodigo),
            status: new Status($parametros->status),
            descricao: new TextoSimples($parametros->descricao),
            dataInicio: new DateTime($parametros->dataInicio),
        );
    }
}