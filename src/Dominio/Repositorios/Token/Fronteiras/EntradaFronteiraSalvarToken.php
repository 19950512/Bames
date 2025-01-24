<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Token\Fronteiras;

final readonly class EntradaFronteiraSalvarToken
{
    public function __construct(
        public string $token,
        public string $codigoContaBancaria,
        public string $bancoNome,
        public string $tempoExpiracao,
        public string $expirarEm,
    ){}
}
