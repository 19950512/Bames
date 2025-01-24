<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado;

use App\Dominio\ObjetoValor\AccessToken;

interface Token
{
    public function encode(array $payload): string;
    public function decode(string $token): object;
}