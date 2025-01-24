<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\VerificarEmail;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

final readonly class ComandoVerificarEmail implements Comando
{
    private string $tokenPronto;

    public function __construct(
        private string $token
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->token)){
            throw new Exception('O token precisa ser informado adequadamente.');
        }

		try {
			$token = new IdentificacaoUnica($this->token);
		}catch (Exception $erro){
			throw new Exception("O token informado está inválido. {$erro->getMessage()}");
		}

		$this->tokenPronto = $token->get();
    }

    public function obterToken(): string
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