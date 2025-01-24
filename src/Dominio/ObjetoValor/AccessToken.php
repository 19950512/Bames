<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

readonly final class AccessToken
{
    public function __construct(
        private string $token
    ){}

    public function get(): string
    {
        return $this->token;
    }
}