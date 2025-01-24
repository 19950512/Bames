<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Token\Fronteiras;

final readonly class SaidaFronteiraToken
{
    public function __construct(
        public string $codigo,
        public string $token,
        public string $expiraEm,
        public string $tokenMomentoCriacao
    ){}
}
