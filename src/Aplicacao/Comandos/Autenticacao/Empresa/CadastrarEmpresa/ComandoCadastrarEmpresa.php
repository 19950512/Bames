<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\ObjetoValor\Senha;
use Exception;
use Override;

final readonly class ComandoCadastrarEmpresa implements Comando
{

	private string $nomeFantasiaPronto;
    private string $numeroDocumentoPronto;
    private string $oabPronto;
	private string $responsavelNomeCompletoPronto;
	private string $responsavelEmailPronto;
	private string $responsavelSenhaPronto;

	public function __construct(
		private string $nomeFantasia,
        private string $numeroDocumento,
        private string $oab,
		private string $responsavelNomeCompleto,
		private string $responsavelEmail,
		private string $responsavelSenha,
	){}

	#[Override] public function executar(): void
	{
		if(empty($this->nomeFantasia)){
			throw new Exception('O nome fantasia precisa ser informado adequadamente.');
		}

		if(empty($this->responsavelNomeCompleto)){
			throw new Exception('O nome completo do responsável precisa ser informado adequadamente.');
		}

		if(empty($this->responsavelEmail)){
			throw new Exception('O e-mail do responsável precisa ser informado adequadamente.');
		}

		if(empty($this->responsavelSenha)){
			throw new Exception('A senha do responsável precisa ser informada adequadamente.');
		}

        if(empty($this->numeroDocumento)){
            throw new Exception('O número do documento precisa ser informado adequadamente.');
        }

        if(empty($this->oab)){
            throw new Exception('O número da OAB precisa ser informado adequadamente.');
        }


		try {
			$senha = new Senha($this->responsavelSenha);
		}catch (Exception $erro) {
			throw new Exception("A senha informada está inválida. {$erro->getMessage()}");
		}


		try {
			$nomeFantasia = new Apelido($this->nomeFantasia);
		}catch (Exception $erro) {
			throw new Exception("O nome fantasia informado está inválido. {$erro->getMessage()}");
		}

		try {
			$responsavelNomeCompleto = new NomeCompleto($this->responsavelNomeCompleto);
		}catch (Exception $erro) {
			throw new Exception("O nome completo do responsável informado está inválido. {$erro->getMessage()}");
		}

		try {
			$email = new Email($this->responsavelEmail);
		}catch (Exception $erro) {
			throw new Exception("O e-mail informado está inválido. {$erro->getMessage()}");
		}

        try {
            $numeroDocumento = new DocumentoDeIdentificacao($this->numeroDocumento);
        }catch (Exception $erro) {
            throw new Exception("O número do documento informado está inválido. {$erro->getMessage()}");
        }

        try {
            $oab = new OAB($this->oab);
        }catch (Exception $erro) {
            throw new Exception("O número da OAB informado está inválido. {$erro->getMessage()}");
        }

		$this->nomeFantasiaPronto = $nomeFantasia->get();
        $this->numeroDocumentoPronto = $numeroDocumento->get();
        $this->oabPronto = $oab->get();
		$this->responsavelEmailPronto = $email->get();
		$this->responsavelNomeCompletoPronto = $responsavelNomeCompleto->get();
		$this->responsavelSenhaPronto = $senha->get();
	}

    public function obterOAB(): string
    {
        return $this->oabPronto;
    }

	public function obterNomeFantasia(): string
	{
		return $this->nomeFantasiaPronto;
	}

	public function obterResponsavelNomeCompleto(): string
	{
		return $this->responsavelNomeCompletoPronto;
	}

	public function obterResponsavelEmail(): string
	{
		return $this->responsavelEmailPronto;
	}

	public function obterResponsavelSenha(): string
	{
		return $this->responsavelSenhaPronto;
	}

    public function obterNumeroDocumento(): string
    {
        return $this->numeroDocumentoPronto;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'nomeFantasia' => $this->nomeFantasiaPronto,
            'numeroDocumento' => $this->numeroDocumentoPronto,
            'oab' => $this->oabPronto,
            'responsavelNomeCompleto' => $this->responsavelNomeCompletoPronto,
            'responsavelEmail' => $this->responsavelEmailPronto,
            'responsavelSenha' => $this->responsavelSenhaPronto,
        ];
    }
}