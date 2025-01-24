<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class SaidaFronteiraEmitirBoleto
{
    public function __construct(
        public string $status,
        public string $codigoBoletoNaPlataformaAPICobranca,
        public string $codigoPagadorNaPlataformaAPICobranca,
        public string $codigoCobrancaNaPlataformaAPICobranca,
        public string $dataEmissao,
        public string $nossoNumero,
        public string $linhaDigitavel,
        public string $codigoBarras,
        public string $urlBoleto,
        public string $respostaCompleta
    ){}
}
