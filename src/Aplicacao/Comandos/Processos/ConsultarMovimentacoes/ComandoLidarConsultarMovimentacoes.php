<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\CNJ;
use Exception;
use Override;

final readonly class ComandoLidarConsultarMovimentacoes implements Comando
{
    private string $CNJPronto;

    public function __construct(
        private string $CNJ,
    ){}
    
    #[Override] public function executar(): void
    {
        if(empty($this->CNJ)){
            throw new Exception('O número do CNJ precisa ser informado adequadamente.');
        }

        try {
            $cnj = new CNJ($this->CNJ);
        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        $this->CNJPronto = $cnj->get();
    }

    public function obterCNJ(): string
    {
        return $this->CNJPronto;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'CNJ' => $this->CNJPronto
        ];
    }
}