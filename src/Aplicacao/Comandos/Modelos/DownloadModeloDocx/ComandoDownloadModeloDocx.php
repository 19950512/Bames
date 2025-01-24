<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\DownloadModeloDocx;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoDownloadModeloDocx implements Comando
{

    private string $empresaCodigoPronto;
    private string $modeloCodigoPronto;

    public function __construct(
        private string $empresaCodigo,
        private string $modeloCodigo,
    ){}

	#[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $modeloCodigo = new IdentificacaoUnica($this->modeloCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do modelo informado está inválido. {$erro->getMessage()}");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->modeloCodigoPronto = $modeloCodigo->get();
    }

    #[Override] public function getPayload(): array
    {

        return [
            'empresaCodigo' => $this->empresaCodigo,
            'modeloCodigo' => $this->modeloCodigo,
        ];
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterModeloCodigo(): string
    {
        return $this->modeloCodigoPronto;
    }
}