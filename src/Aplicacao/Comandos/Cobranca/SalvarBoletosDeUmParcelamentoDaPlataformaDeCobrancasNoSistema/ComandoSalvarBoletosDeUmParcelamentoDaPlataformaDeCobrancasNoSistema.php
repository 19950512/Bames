<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\SalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

readonly final class ComandoSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema implements Comando
{

    private string $empresaCodigoPronto;
    private string $codigoParcelamentoNaPlataformaDeCobrancaPronto;
    private string $contaBancairaCodigoPronto;

    public function __construct(
        private string $empresaCodigo,
        private string $contaBancariaCodigo,
        private string $codigoParcelamentoNaPlataformaDeCobranca,
    ){}

    #[Override] public function executar(): void
    {
        if (empty($this->empresaCodigo)) {
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        if (empty($this->codigoParcelamentoNaPlataformaDeCobranca)) {
            throw new Exception('O código do parcelamento na plataforma de cobrança precisa ser informado adequadamente.');
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        } catch (Exception $erro) {
            throw new Exception("O código da empresa precisa ser informado adequadamente.");
        }

        try {
            $contaBancariaCodigo = new IdentificacaoUnica($this->contaBancariaCodigo);
        } catch (Exception $erro) {
            throw new Exception("O código da conta bancária precisa ser informado adequadamente.");
        }

        $this->codigoParcelamentoNaPlataformaDeCobrancaPronto = $this->codigoParcelamentoNaPlataformaDeCobranca;
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->contaBancairaCodigoPronto = $contaBancariaCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'contaBancariaCodigo' => $this->contaBancariaCodigo,
            'codigoParcelamentoNaPlataformaDeCobranca' => $this->codigoParcelamentoNaPlataformaDeCobranca,
        ];
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterCodigoParcelamentoNaPlataformaDeCobranca(): string
    {
        return $this->codigoParcelamentoNaPlataformaDeCobrancaPronto;
    }

    public function obterCodigoContaBancaria(): string
    {
        return $this->contaBancairaCodigoPronto;
    }


}