<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\ExcluirModelo;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;

final readonly class ComandoExcluirModelo implements Comando
{

    private string $codigoModeloPronto;
    private string $empresaCodigoPronto;

    public function __construct(
        private string $codigoModelo,
        private string $empresaCodigo
    ){}

	#[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $codigoModelo = new IdentificacaoUnica($this->codigoModelo);
        }catch(Exception $erro){
            throw new Exception("O código do modelo informado está inválido. {$erro->getMessage()}");
        }

        $this->codigoModeloPronto = $codigoModelo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'codigoModelo' => $this->codigoModelo,
            'empresaCodigo' => $this->empresaCodigo,
        ];
    }

    public function obterCodigoModelo(): string
    {
        return $this->codigoModeloPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

}