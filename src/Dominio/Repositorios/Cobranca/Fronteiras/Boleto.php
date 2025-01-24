<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Cobranca\Fronteiras;

readonly final class Boleto
{
    public function __construct(
        public string $boletoCodigo,
        public string $boletoCodigoNaPlataformaCobrancaAPI,
        public string $cobrancaCodigo,
        public string $pagadorCodigo,
        public string $status,
        public string $vencimento,
        public string $nossoNumero,
        public string $codigoDeBarras,
        public string $linhaDigitavel,
        public string $linkBoleto,
        public string $mensagem,
        public float $valor,
    ){}
}