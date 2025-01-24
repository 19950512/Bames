<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Boleto\Fronteiras;

readonly final class EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI
{
    public function __construct(
        public string $codigoBoletoNaPlataformaAPI,
        public string $codigoPagadorIDPlataformaAPI,
        public string $codigoCobrancaNaPlataformaAPI,
        public string $respostaCompletaDaPlataforma,
        public string $empresaCodigo,
        public string $status,
        public string $nossoNumero,
        public string $codigoDeBarras,
        public string $linhaDigitavel,
        public string $urlBoleto,
        public string $mensagem,
        public float $valor,
        public int $parcela,
    ){}
}
