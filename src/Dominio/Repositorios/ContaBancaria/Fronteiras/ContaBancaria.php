<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ContaBancaria\Fronteiras;

final class ContaBancaria
{
    private array $eventos = [];
    public function __construct(
        public string $contaBancariaCodigo,
        public string $nome,
        public string $banco,
        public string $ambiente,
        public string $chaveAPI,
        public string $clientIDAPI,
    ){}

    public function adicionarEvento(string $momento, string $descricao): void
    {
        $this->eventos[] = [
            'momento' => date('d/m/Y', strtotime($momento)).' às '.date('H:i', strtotime($momento)),
            'descricao' => $descricao
        ];
    }

    public function obterEventos(): array
    {
        return $this->eventos;
    }
}