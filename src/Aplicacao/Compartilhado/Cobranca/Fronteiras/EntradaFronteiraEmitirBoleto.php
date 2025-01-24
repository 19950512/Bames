<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class EntradaFronteiraEmitirBoleto
{
    public function __construct(
        public string $clientIDAPI,
        public string $chaveAPI,
        public float $valor,
        public string $vencimento,
        public float $juros,
        public string $tipoJuros,
        public string $tipoCobranca,
        public float $multa,
        public string $tipoMulta,
        public string $mensagem,
        public float $desconto,
        public int $parcelas,
        public string $pagadorNomeCompleto,
        public string $pagadorDocumentoNumero,
        public string $pagadorEmail,
        public string $pagadorTelefone,
        public bool $contaBancariaAmbienteProducao,
    ) {}
}
