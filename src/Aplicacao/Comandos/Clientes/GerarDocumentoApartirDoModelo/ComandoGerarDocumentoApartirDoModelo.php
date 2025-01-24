<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

readonly final class ComandoGerarDocumentoApartirDoModelo implements Comando
{

    private string $modeloIDPronto;
    private string $clienteIDPronto;

    public function __construct(
        private string $modeloID,
        private string $clienteID,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->modeloID)){
            throw new Exception('O ID do modelo precisa ser informado adequadamente.');
        }

        if(empty($this->clienteID)){
            throw new Exception('O ID do cliente precisa ser informado adequadamente.');
        }

        try {
            $modeloID = new IdentificacaoUnica($this->modeloID);
        }catch (Exception $erro){
            throw new Exception("O ID do modelo precisa ser informado adequadamente.");
        }

        try {
            $clienteID = new IdentificacaoUnica($this->clienteID);
        }catch (Exception $erro){
            throw new Exception("O ID do cliente precisa ser informado adequadamente.");
        }

        $this->modeloIDPronto = $modeloID->get();
        $this->clienteIDPronto = $clienteID->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'modeloID' => $this->modeloID,
            'clienteID' => $this->clienteID,
        ];
    }

    public function obterModeloID(): string
    {
        return $this->modeloIDPronto;
    }

    public function obterClienteID(): string
    {
        return $this->clienteIDPronto;
    }
}