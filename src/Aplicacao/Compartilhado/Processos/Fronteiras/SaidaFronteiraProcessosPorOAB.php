<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Processos\Fronteiras;

use App\Aplicacao\Compartilhado\Processos\Fronteiras\Processo;

final class SaidaFronteiraProcessosPorOAB
{
    private array $processos = [];
    
    public function __construct(
        public array $payload_request,
        public array $payload_response,
        public string $nomeCompleto,
        public string $tipo,
        public int $quantidadeDeProcessos = 0,
    ){}

    public function adicionar(object $processo): void
    {
        $this->processos[] = $processo;
    }

    public function get(): array
    {
        return $this->processos;
    }
}

