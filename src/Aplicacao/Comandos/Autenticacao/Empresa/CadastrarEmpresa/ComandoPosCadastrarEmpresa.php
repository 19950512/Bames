<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

final readonly class ComandoPosCadastrarEmpresa implements Comando
{

	private string $empresaCodigoPronto;

	public function __construct(
		private string $empresaCodigo,
	){}

	#[Override] public function executar(): void
	{
        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro) {
            throw new Exception("O código da empresa está inválido. {$erro->getMessage()}");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
	}

    #[Override] public function getPayload(): array
    {
        return [
            'codigoEmpresa' => $this->empresaCodigo,
        ];
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }
}