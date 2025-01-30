<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Contrato\Fronteiras;

final readonly class EntradaFronteiraCriarContrato
{
    public function __construct(
        public string $codigo,
        public string $clienteCodigo,
        public string $contaBancariaCodigo,
        public string $empresaCodigo,
        public string $status,
        public bool $recorrente,
        public string $dataInicio,
        public string $dataCriacao,
        public string $meioPagamento,
        public int $diaVencimento,
        public int $horarioEmissaoCobrancaHora,
        public int $horarioEmissaoCobrancaMinuto,
        public int $diaEmissaoCobranca,
        public int $parcela,
        public float $valor,
        public float $multa,
        public float $juros,
        public float $descontoAntecipacao,
        public string $tipoDescontoAntecipacao,
        public string $tipoJuro,
        public string $tipoMulta,
    ){}
}