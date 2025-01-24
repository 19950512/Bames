<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Atendimento\Fronteiras;

final readonly class EntradaFronteiraNovoAtendimento
{
    public function __construct(
        public string $empresaCodigo,
        public string $clienteCodigo,
        public string $atendimentoCodigo,
        public string $descricao,
        public string $status,
        public string $usuarioCodigo,
    ){}
}