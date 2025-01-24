<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Agenda\Fronteiras;

readonly final class CompromissoAgenda
{
    public function __construct(
        public string $codigo,
        public string $business_id,
        public string $usuario_id,
        public string $plataforma_id,
        public string $titulo,
        public string $descricao,
        public string $status,
        public string $dataInicio,
        public string $dataFim,
        public string $momento,
        public bool $diaTodo,
        public int $recorrencia,
    ){}
}