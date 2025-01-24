<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

readonly final class ComandoBoletoFoiAceitoNaPlataforma implements Comando
{

    private string $empresaCodigoPronto;
    private string $boletoCodigoNaPlataformaPronto;
    public function __construct(
        public string $empresaCodigo,
        public string $boletoCodigoNaPlataforma,
    ){}

    #[Override] public function executar(): void
    {

        if(empty($this->empresaCodigo)){
            throw new Exception("Ops, o parâmetro empresa código não pode ser vazio.");
        }

        if(empty($this->boletoCodigoNaPlataforma)){
            throw new Exception("Ops, o parâmetro boleto código na plataforma não pode ser vazio.");
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $e){
            throw new Exception("Ops, o parâmetro empresa código não é válido.");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->boletoCodigoNaPlataformaPronto = $this->boletoCodigoNaPlataforma;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'boletoCodigoNaPlataforma' => $this->boletoCodigoNaPlataforma,
        ];
    }

    public function obterEmpresaCodigoPronto(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterBoletoCodigoNaPlataformaPronto(): string
    {
        return $this->boletoCodigoNaPlataformaPronto;
    }
}