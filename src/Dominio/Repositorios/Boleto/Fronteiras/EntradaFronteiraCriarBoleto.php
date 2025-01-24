<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Boleto\Fronteiras;

readonly final class EntradaFronteiraCriarBoleto
{
    public function __construct(
        public string $empresaCodigo,
        public string $boleto_id,
        public string $cobranca_id,
        public string $cliente_id,
        public string $conta_bancaria_id,
        public float $valor,
        public string $data_vencimento,
        public string $mensagem,
        public float $multa,
        public float $juros,
        public string $seu_numero,
        public string $status,
        public string $boletoCodigoNaPlataforma = '',
        public string $cobrancaCodigoNaPlataforma = '',
    ){}
}
