<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Agenda\Fronteiras;

readonly final class EntradaFronteiraAtualizarEvento
{
    public function __construct(
        public string $eventoCodigo,
        public string $titulo,
        public string $descricao,
        public bool $diaTodo,
        public int $recorrencia,
        public string $horarioInicio,
        public string $horarioFim,
    ){}
}