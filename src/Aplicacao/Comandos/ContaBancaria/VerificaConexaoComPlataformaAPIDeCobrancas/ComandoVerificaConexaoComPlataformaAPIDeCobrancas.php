<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\ContaBancaria\VerificaConexaoComPlataformaAPIDeCobrancas;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;

readonly final class ComandoVerificaConexaoComPlataformaAPIDeCobrancas implements Comando
{

    private string $empresaCodigoPronto;
    private string $codigoContaBancariaPronto;

    public function __construct(
        private string $empresaCodigo,
        private string $codigoContaBancaria,
    ){}

    #[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $codigoContaBancaria = new IdentificacaoUnica($this->codigoContaBancaria);
        }catch (Exception $erro) {
            throw new Exception("O código da conta bancária informado está inválido. {$erro->getMessage()}");
        }

        $this->codigoContaBancariaPronto = $codigoContaBancaria->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'codigoContaBancaria' => $this->codigoContaBancaria,
        ];
    }

    public function getEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function getCodigoContaBancaria(): string
    {
        return $this->codigoContaBancariaPronto;
    }
}