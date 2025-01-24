<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Boleto\Fronteiras;

readonly final class SaidaFronteiraBoleto
{
    public function __construct(
        public string $codigoBoleto,
        public string $empresaCodigo,
        public string $cobrancaCodigo,
        public string $codigoBoletoNaPlataformaAPICobranca,
        public string $contaBancariaCodigo,
        public float $valor,
        public string $dataVencimento,
        public string $statusBoleto,
        public string $linkBoleto,
        public string $nossoNumero,
        public string $seuNumero,
        public string $codigoDeBarras,
        public string $linhaDigitavel,
        public string $mensagem,
        public string $pagadorCodigo,
        public string $qrCode,
        public bool $foiAceitoPelaPlataforma
    ){}
}
