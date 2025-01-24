<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class BoletoParcelamento
{
    public function __construct(
        public string $codigoBoletoNaPlataforma,
        public string $dataVencimento,
        public string $pagadorCodigoNaPlataforma,
        public string $pagadorNomeCompleto,
        public string $pagadorDocumento,
        public string $pagadorEmail,
        public string $pagadorTelefone,
        public string $nossoNumero,
        public string $codigoDeBarras,
        public string $linhaDigitavel,
        public string $descricao,
        public string $status,
        public string $urlBoleto,
        public string $respostaCompletaDaPlataforma,
        public float $valor,
        public float $multa,
        public float $juros,
        public int $parcela,
        public string $codigoCobrancaNaPlataformaAPI,
    ){}
}
