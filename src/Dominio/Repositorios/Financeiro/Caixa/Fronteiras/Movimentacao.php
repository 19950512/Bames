<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras;

readonly final class Movimentacao
{
    public function __construct(
        public int $planoDeContaCodigo,
        public string $planoDeContaNome,
        public string $codigoMovimentacao,
        public float $valor,
        public string $descricao,
        public string $dataMovimentacao,
        public string $pagadorCodigo = '',
        public string $pagadorDocumento = '',
        public string $pagadorNomeCompleto = '',
        public string $cobrancaCodigo = '',
        public string $boletoCodigo = '',
    ){}
}