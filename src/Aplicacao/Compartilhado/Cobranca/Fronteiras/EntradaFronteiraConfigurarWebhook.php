<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

final class EntradaFronteiraConfigurarWebhook
{
    public function __construct(
        public string $chaveAPI,
        public string $webhookURL,
        public string $webhookCodigo,
        public bool $contaBancariaAmbienteProducao,
    ) {}
}