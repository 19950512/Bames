<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Token;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ImplementacaoTokenFirebaseJWT implements Token
{

    public function __construct(
        private Ambiente $ambiente
    ){}

    public function encode(array $payload): string
    {
        $jwt = JWT::encode(
            payload: $payload,
            key: $this->ambiente->get('JWT_KEY'),
            alg: $this->ambiente->get('JWT_ALG')
        );

        return $jwt;
    }

    public function decode(string $token): object
    {
        return (object) JWT::decode($token, new Key($this->ambiente->get('JWT_KEY'), $this->ambiente->get('JWT_ALG')));
    }
}
