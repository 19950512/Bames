<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class EntradaObterTodosOsBoletosDoParcelamento
{
    public function __construct(
        public bool $contaBancariaAmbienteProducao,
        public string $chaveAPI,
        public string $codigoParcelamento,
    ){}
}