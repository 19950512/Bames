<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Atendimento\Fronteiras;

final readonly class SaidaFronteiraBuscarAtendimento
{
    public function __construct(
        public string $atendimentoCodigo,
        public string $empresaCodigo,
        public string $clienteCodigo,
        public string $descricao,
        public string $status,
        public string $dataInicio,
        public string $usuarioCodigo,
    ){}
}