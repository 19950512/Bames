<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Agenda\Fronteiras;

final class SaidaFronteiraBuscarEvento
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

    public function toArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'diaTodo' => $this->diaTodo,
        ];
    }
}