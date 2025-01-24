<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class EntradaFronteiraConsultarBoleto
{
    public function __construct(
        public string $codigoBoletoNaPlataformaAPICobranca,
        public string $chaveAPI,
        public bool $contaBancariaAmbienteProducao
    ){}
}