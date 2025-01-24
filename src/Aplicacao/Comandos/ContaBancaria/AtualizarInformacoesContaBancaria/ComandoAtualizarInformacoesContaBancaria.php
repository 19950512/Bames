<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\ContaBancaria\AtualizarInformacoesContaBancaria;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use Exception;
use Override;

readonly final class ComandoAtualizarInformacoesContaBancaria implements Comando
{

    private string $empresaCodigoPronto;
    private string $codigoContaBancariaPronto;
    private string $nomeContaBancariaPronto;
    private string $chaveAPIContaBancariaPronto;
    private string $clientIDContaBancariaPronto;
    private string $ambientePronto;

    public function __construct(
        private string $empresaCodigo,
        private string $codigoContaBancaria,
        private string $nomeContaBancaria,
        private string $chaveAPIContaBancaria,
        private string $clientIDContaBancaria,
        private string $ambiente,
    ){}

    #[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $codigoContaBancaria = new IdentificacaoUnica($this->codigoContaBancaria);
        }catch (Exception $erro){
            throw new Exception("O código da conta bancária informado está inválido. {$erro->getMessage()}");
        }

        try {
            $nomeContaBancaria = new TextoSimples($this->nomeContaBancaria);
        }catch (Exception $erro){
            throw new Exception("O nome da conta bancária informado está inválido. {$erro->getMessage()}");
        }

        try {
            $chaveAPIContaBancaria = new TextoSimples($this->chaveAPIContaBancaria);
        }catch (Exception $erro){
            throw new Exception("A chave API da conta bancária informada está inválida. {$erro->getMessage()}");
        }

        try {
            $clientIDContaBancaria = new TextoSimples($this->clientIDContaBancaria);
        }catch (Exception $erro){
            throw new Exception("O client ID da conta bancária informado está inválido. {$erro->getMessage()}");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->ambientePronto = $this->ambiente;
        $this->codigoContaBancariaPronto = $codigoContaBancaria->get();
        $this->nomeContaBancariaPronto = $nomeContaBancaria->get();
        $this->chaveAPIContaBancariaPronto = $chaveAPIContaBancaria->get();
        $this->clientIDContaBancariaPronto = $clientIDContaBancaria->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'codigoContaBancaria' => $this->codigoContaBancaria,
            'nomeContaBancaria' => $this->nomeContaBancaria,
            'chaveAPIContaBancaria' => $this->chaveAPIContaBancaria,
            'clientIDContaBancaria' => $this->clientIDContaBancaria,
            'ambiente' => $this->ambiente,
        ];
    }

    public function obterAmbiente(): string
    {
        return $this->ambientePronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterCodigoContaBancaria(): string
    {
        return $this->codigoContaBancariaPronto;
    }

    public function obterNomeContaBancaria(): string
    {
        return $this->nomeContaBancariaPronto;
    }

    public function obterChaveAPIContaBancaria(): string
    {
        return $this->chaveAPIContaBancariaPronto;
    }

    public function obterClientIDContaBancaria(): string
    {
        return $this->clientIDContaBancariaPronto;
    }
}