<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Email;
use Exception;
use Override;

final readonly class ComandoLoginEmailESenha implements Comando
{

	private string $emailPronto;
	private string $senhaPronto;
	public function __construct(
		private string $email,
		private string $senha
	){}

	#[Override] public function executar(): void
	{

		$limiteMaximoCaracteresSenha = 50;
		$limiteMinimoCaracteresSenha = 9;

		if(empty($this->email)){
			throw new Exception('O e-mail precisa ser informado adequadamente.');
		}

		if(empty($this->senha)){
			throw new Exception('A senha precisa ser informada adequadamente.');
		}

		if(strlen($this->senha) < $limiteMinimoCaracteresSenha){
			throw new Exception("A senha precisa ter no mínimo $limiteMinimoCaracteresSenha caracteres.");
		}

		if(strlen($this->senha) > $limiteMaximoCaracteresSenha){
			throw new Exception("A senha atingiu o limite máximo de $limiteMaximoCaracteresSenha caracteres.");
		}

		try {
			$email = new Email($this->email);
		}catch (Exception $erro){
			throw new Exception("O e-mail informado está inválido. {$erro->getMessage()}");
		}

		$this->senhaPronto = $this->senha;
		$this->emailPronto = $email->get();
	}

	public function obterEmail(): string
	{
		return $this->emailPronto;
	}

	public function obterSenha(): string
	{
		return $this->senhaPronto;
	}

    #[Override] public function getPayload(): array
    {
        return [
            'email' => $this->emailPronto,
            'senha' => $this->senhaPronto,
        ];
    }
}