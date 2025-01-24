<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Usuarios\AdicionarCargo;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

readonly class ComandoAdicionarCargo implements Comando
{

    private string $empresaID;
    private string $usuarioID;
    private string $cargoID;

    public function __construct(
        private string $usuarioCodigo,
        private string $cargoCodigo,
        private string $empresaCodigo,
    ){}

	#[Override] public function executar(): void
    {
        if(empty($this->usuarioCodigo)){
            throw new Exception('O código do usuário precisa ser informado adequadamente.');
        }

        if(empty($this->cargoCodigo)){
            throw new Exception('O código do cargo precisa ser informado adequadamente.');
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do usuário informado está inválido. {$erro->getMessage()}");
        }

        try {
            $cargoCodigo = new IdentificacaoUnica($this->cargoCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do cargo informado está inválido. {$erro->getMessage()}");
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        $this->usuarioID = $usuarioCodigo->get();
        $this->cargoID = $cargoCodigo->get();
        $this->empresaID = $empresaCodigo->get();
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioID;
    }

    public function obterCargoCodigo(): string
    {
        return $this->cargoID;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaID;
    }

    public function getPayload(): array
    {
        return [
            'usuario_codigo' => $this->usuarioCodigo,
            'cargo_codigo' => $this->cargoCodigo,
            'empresa_codigo' => $this->empresaCodigo,
        ];
    }
}