<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Webhook\Fronteiras;

final readonly class EntradaFronteiraSalvarWebhook
{
    public function __construct(
        public string $eventID,
        public string $payload,
        public string $headers,
        public string $ip,
        public string $userAgent,
        public string $metodo,
        public string $uri,
        public string $parceiro,
        public string $momento
    ){}
}
