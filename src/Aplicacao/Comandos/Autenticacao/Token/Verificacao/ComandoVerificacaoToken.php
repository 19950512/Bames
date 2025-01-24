<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Token\Verificacao;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoVerificacaoToken implements Comando
{
    private string $tokenPronto;
    private string $contaCodigoPronto;
    private string $empresaCodigoPronto;

    public function __construct(
        private string $token,
        private string $contaCodigo,
        private string $empresaCodigo,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->token)){
            throw new Exception('O token precisa ser informado.');
        }

        if(empty($this->contaCodigo)){
            throw new Exception('O código da conta precisa ser informado.');
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado.');
        }

        try {
            $contaCodigo = new IdentificacaoUnica($this->contaCodigo);
        }catch (Exception $erro){
            throw new Exception('O código da conta informado está inválido.');
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception('O código da empresa informado está inválido.');
        }

        $this->contaCodigoPronto = $contaCodigo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
		$this->tokenPronto = $this->token;
    }

    public function obterContaCodigo(): string
    {
        return $this->contaCodigoPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
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