<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Request\Fronteiras;

final readonly class EntradaFronteiraSalvarEventosDoRequest
{
    public function __construct(
        public string $comandoPayload,
        public string $comando,
        public string $usuarioId,
        public string $businessId,
        public string $requestCodigo,
        public string $momento,
        public int $totalEventos,
        public array $eventos,
        public string $accessToken = ''
    ){}
}