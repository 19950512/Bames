<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Cobranca\Fronteiras;

readonly final class EntradaFronteiraCriarUmaCobranca
{
    public function __construct(
        public string $cobrancaCodigo,
        public string $empresaCodigo,
        public string $contaBancariaCodigo,
        public string $clienteCodigo,
        public string $clienteNomeCompleto,
        public string $dataVencimento,
        public string $mensagem,
        public string $meioDePagamento,
        public array $composicaoDaCobranca,
        public float $multa,
        public float $juros,
        public float $valorDescontoAntecipacao,
        public string $tipoDesconto,
        public string $tipoJuros,
        public string $tipoMulta,
        public int $parcela,
    ){}
}