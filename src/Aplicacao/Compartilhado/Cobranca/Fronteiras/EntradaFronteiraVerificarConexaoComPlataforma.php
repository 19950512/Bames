<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

readonly final class EntradaFronteiraVerificarConexaoComPlataforma
{
    public function __construct(
        public string $chaveAPI,
        public bool $contaBancariaAmbienteProducao,
    ){}
}
