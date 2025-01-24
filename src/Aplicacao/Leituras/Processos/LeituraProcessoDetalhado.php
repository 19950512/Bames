<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Processos;

use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

final readonly class LeituraProcessoDetalhado
{
    public function __construct(
        private RepositorioProcessos $repositorioProcessos,
        private EntidadeEmpresarial $entidadeEmpresarial
    ){}

    public function executar(string $processoCodigo): array
    {
        return $this->repositorioProcessos->obterDetalhesDoProcesso(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
            processoCodigo: $processoCodigo
        )->toArray();
    }
}