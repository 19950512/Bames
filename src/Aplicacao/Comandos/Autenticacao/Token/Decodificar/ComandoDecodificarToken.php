<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Token\Decodificar;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;

final readonly class ComandoDecodificarToken implements Comando
{
    private string $tokenPronto;

    public function __construct(
        private string $token,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->token)){
            throw new Exception('O token precisa ser informado.');
        }

        $this->tokenPronto = $this->token;
    }
    
    public function obtertoken(): string
    {
        return $this->tokenPronto;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'token' => $this->tokenPronto,
        ];
    }
}
