<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\AlterarSenha;

use Override;
use Exception;
use App\Dominio\ObjetoValor\Senha;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoAlterarSenha implements Comando
{
    private string $tokenPronto;
    private string $senhaPronto;

    public function __construct(
        private string $token,
        private string $senha,
        private string $confirmacaoSenha,
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

		try {
			$senha = new Senha($this->senha);
		}catch (Exception $erro) {
			throw new Exception("A senha informada está inválida. {$erro->getMessage()}");
		}

        if($senha->get() !== $this->confirmacaoSenha){
            throw new Exception('A senha e a confirmação da senha não conferem.');
        }

		$this->senhaPronto = $senha->get();
        $this->tokenPronto = $token->get();
    }

    public function obterSenha(): string
    {
        return $this->senhaPronto;
    }
    public function obterToken(): string
    {
        return $this->tokenPronto;
    }
    #[Override] public function getPayload(): array
    {
        return [
            'token' => $this->tokenPronto,
            'senha' => $this->senhaPronto,
        ];
    }
}

