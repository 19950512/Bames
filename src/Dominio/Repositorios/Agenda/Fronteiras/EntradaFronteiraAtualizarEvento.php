<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Agenda\Fronteiras;

final class EntradaFronteiraAtualizarEvento
{
    public function __construct(
        public string $codigo,
        public string $business_id,
        public string $usuario_id,
        public string $titulo,
        public string $descricao,
        public string $dataInicio,
        public string $dataFim,
        public string $momento,
        public string $status,
        public string $plataforma_evento_id,
        public bool $diaTodo,
        public int $recorrencia,
    ){}
}