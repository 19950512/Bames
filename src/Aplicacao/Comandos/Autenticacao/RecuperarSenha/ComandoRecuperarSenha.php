<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\RecuperarSenha;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Email;
use Exception;
use Override;

final readonly class ComandoRecuperarSenha implements Comando
{
    private string $emailPronto;

    public function __construct(
        private string $email
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->email)){
            throw new Exception('O e-mail precisa ser informado adequadamente.');
        }

		try {
			$email = new Email($this->email);
		}catch (Exception $erro){
			throw new Exception("O e-mail informado está inválido. {$erro->getMessage()}");
		}

		$this->emailPronto = $email->get();
    }

    public function obterEmail(): string
    {
        return $this->emailPronto;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'email' => $this->emailPronto,
        ];
    }
}