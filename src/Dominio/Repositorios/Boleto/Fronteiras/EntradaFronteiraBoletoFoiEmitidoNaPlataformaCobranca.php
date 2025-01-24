<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Boleto\Fronteiras;

readonly final class EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca
{
    public function __construct(
        public string $business_id,
        public string $boleto_id,

        public string $cobranca_id_plataforma_API_cobranca,
        public string $boleto_id_plataforma_API_cobranca,
        public string $boleto_pagador_id_plataforma_API_cobranca,
        public string $nosso_numero,
        public string $linha_digitavel,
        public string $codigo_barras,
        public string $url,
        public string $status,
        public string $respostaCompletaDaPlataforma
    ){}
}
