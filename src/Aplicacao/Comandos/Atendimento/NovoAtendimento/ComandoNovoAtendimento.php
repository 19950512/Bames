<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Atendimento\NovoAtendimento;

use DateTime;
use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final class ComandoNovoAtendimento implements Comando
{

    private string $empresaCodigoPronto;
    private string $clienteCodigoPronto;
    private string $descricaoPronto;

    public function __construct(
        public string $empresaCodigo,
        public string $clienteCodigo,
        public string $descricao,
    ){}

	#[Override] public function executar(): void
	{
        
        try {

            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código da empresa informado está inválido.');
        }

        try {

            $clienteCodigo = new IdentificacaoUnica($this->clienteCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código do cliente informado está inválido.');
        }

        try {

            $descricao = new TextoSimples($this->descricao);
        } catch (Exception $erro) {
            throw new Exception('A descrição informada está inválida.');
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->clienteCodigoPronto = $clienteCodigo->get();
        $this->descricaoPronto = $descricao->get();
	}

	#[Override] public function getPayload(): array
	{
		return [
            'empresaCodigo' => $this->empresaCodigo,
            'clienteCodigo' => $this->clienteCodigo,
            'descricao' => $this->descricao,
        ];
	}

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterClienteCodigo(): string
    {
        return $this->clienteCodigoPronto;
    }

    public function obterDescricao(): string
    {
        return $this->descricaoPronto;
    }

}