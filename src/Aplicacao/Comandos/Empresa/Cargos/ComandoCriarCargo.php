<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Cargos;

use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoCriarCargo implements Comando
{
    private string $nomePronto;
    private string $usuarioID;
    private string $empresaID;

    public function __construct(
        private string $empresaCodigo,
        private string $usuarioCodigo,
        private string $nome,
    ){}

    public function executar(): void
    {

        if(empty($this->nome)){
            throw new Exception('O nome precisa ser informado adequadamente.');
        }

        try {
            $nome = new Apelido($this->nome);
        }catch(Exception $erro){
            throw new Exception("O nome informado está inválido. {$erro->getMessage()}");
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da usuário informado está inválido. {$erro->getMessage()}");
        }
		
        $this->usuarioID = $usuarioCodigo->get();
        $this->empresaID = $empresaCodigo->get();
        $this->nomePronto = $nome->get();
    }

    public function getPayload(): array
    {
        return [
            'empresa_codigo' => $this->empresaCodigo,
            'usuario_codigo' => $this->usuarioID,
            'nome' => $this->nomePronto,
        ];
    }
    public function obterNome(): string
    {
        return $this->nomePronto;
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioID;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigo;
    }
}