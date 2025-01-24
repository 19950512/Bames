<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\OAB;
use Exception;
use Override;

final readonly class ComandoLidarConsultasProcessoPorOAB implements Comando
{

    private string $OABPronto;
    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;

    public function __construct(
        private string $OAB,
        private string $empresaCodigo,
        private string $usuarioCodigo,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->OAB)){
            throw new Exception('O número da OAB precisa ser informado adequadamente.');
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        if(empty($this->usuarioCodigo)){
            throw new Exception('O código do usuário precisa ser informado adequadamente.');
        }

        try {
            $oab = new OAB(mb_strtoupper($this->OAB));
        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da empresa precisa ser informado adequadamente.");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch (Exception $erro){
            throw new Exception("O código do usuário precisa ser informado adequadamente.");
        }

        $this->OABPronto = $oab->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
    }

    public function obterOAB(): string
    {
        return $this->OABPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioCodigoPronto;
    }

    #[Override]
    public function getPayload(): array
    {
        return [
            'OAB' => $this->OAB,
            'empresa_codigo' => $this->empresaCodigo,
            'usuario_codigo' => $this->usuarioCodigo
        ];
    }
}
