<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Webhook;

use App\Dominio\Repositorios\Webhook\Fronteiras\EntradaFronteiraSalvarWebhook;

interface RepositorioWebhook
{
    public function salvarWebhook(EntradaFronteiraSalvarWebhook $parametros): void;
    public function verificarWebhookRecebido(string $eventID): bool;
}