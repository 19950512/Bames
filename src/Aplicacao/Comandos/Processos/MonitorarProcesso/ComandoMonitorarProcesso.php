<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos\MonitorarProcesso;

use Override;
use Exception;
use App\Dominio\ObjetoValor\CNJ;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoMonitorarProcesso implements Comando
{
    private string $CNJPronto;
    private string $empresaCodigoPronto;

    public function __construct(
        private string $CNJ,
        private string $empresaCodigo,
    ){}
    
    #[Override] public function executar(): void
    {
        if(empty($this->CNJ)){
            throw new Exception('O número do CNJ precisa ser informado adequadamente.');
        }
        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        try {
            $cnj = new CNJ($this->CNJ);
        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->CNJPronto = $cnj->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'CNJ' => $this->CNJ,
            'empresaCodigo' => $this->empresaCodigo
        ];
    }

    public function obterCNJ(): string
    {
        return $this->CNJPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }
}