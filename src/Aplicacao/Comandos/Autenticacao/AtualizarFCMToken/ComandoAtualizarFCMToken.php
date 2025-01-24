<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\AtualizarFCMToken;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

final readonly class ComandoAtualizarFCMToken implements Comando
{
    private string $FCMTokenPronto;

    public function __construct(
        private string $FCMToken,
    ){}

    #[Override] public function executar(): void
    {

        if(empty($this->FCMToken)){
            throw new Exception('O FCM Token precisa ser informado adequadamente.');
        }

        $this->FCMTokenPronto = $this->FCMToken;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'FCMToken' => $this->FCMToken,
        ];
    }

    public function obterFCMToken(): string
    {
        return $this->FCMTokenPronto;
    }
}