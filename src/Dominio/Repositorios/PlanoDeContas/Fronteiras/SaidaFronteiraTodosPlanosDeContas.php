<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\PlanoDeContas\Fronteiras;

final class SaidaFronteiraTodosPlanosDeContas
{
    private array $planosDeContas = [];
    public function __construct(){}

    public function adicionarPlanoDeConta(SaidaFronteiraPlanoDeConta $planoDeContas): void
    {
        $this->planosDeContas[] = $planoDeContas;
    }

    public function obterPlanosDeContas(): array
    {
        return $this->planosDeContas;
    }
};
