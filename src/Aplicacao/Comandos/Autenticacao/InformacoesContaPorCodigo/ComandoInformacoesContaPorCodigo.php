<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\InformacoesContaPorCodigo;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoInformacoesContaPorCodigo implements Comando
{

	private string $contaCodigoPronto;
	public function __construct(
		private string $contaCodigo
	){}

	#[Override] public function executar(): void
	{

        if(empty($this->contaCodigo)){
            throw new Exception('Código da conta não informado');
        }

        try {
            $contaCodigo = new IdentificacaoUnica($this->contaCodigo);
        }catch(Exception $e){
            throw new Exception('Erro ao buscar informações da conta');
        }

        $this->contaCodigoPronto = $contaCodigo->get();
    }

    public function obterContaCodigo(): string
    {
        return $this->contaCodigoPronto;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'conta_codigo' => $this->contaCodigo
        ];
    }
}