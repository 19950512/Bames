<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras;

readonly final class FronteiraEntradaLancarMovimentacaoNoCaixa
{
    public function __construct(
        public string $movimentacaoCodigo,
        public float $valor,
        public string $descricao,
        public int $planoDeContaCodigo,
        public string $planoDeContaNome,
        public string $dataMovimentacao,
        public string $contaBancariaCodigo,
        public string $empresaCodigo,
        public string $usuarioCodigo,
        public ?string $boletoCodigo = null,
        public ?string $boletoNossoNumero = null,
        public ?string $cobrancaCodigo = null,
        public ?string $pagadorCodigo = null,
        public ?string $pagadorNomeCompleto = null,
        public ?string $pagadorDocumento = null,
    ){}
}