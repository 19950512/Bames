<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa;

use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Override;

final readonly class ComandoDeletarTudoRelacionadoAEmpresa implements Comando
{

    private string $empresaID;
    public function __construct(
        private string $empresaCodigo,
    ){}

	#[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        $this->empresaID = $empresaCodigo->get();
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaID;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaID
        ];
    }
}