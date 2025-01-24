<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras;

final class EntradaFronteiraSalvarProcesso
{

    public function __construct(
        public string $business_id,
        public string $processo_codigo,
        public string $processo_numero_cnj,
        public string $processo_data_ultima_movimentacao,
        public string $processo_quantidade_movimentacoes,
        public string $processo_demandante,
        public string $processo_demandado,
        public string $processo_ultima_movimentacao_descricao,
        public string $processo_ultima_movimentacao_data,
        public string $oab_ou_documento_consultada,
    ){}

    public array $fontes = [];
    public function addFonte(Fonte $fonte): void
    {
        $this->fontes[] = $fonte;
    }
}
