<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ContaBancaria\Fronteiras;

readonly final class EntradaFronteiraAtualizarContaBancaria
{
    public function __construct(
        public string $empresaCodigo,
        public string $contaBancariaCodigo,
        public string $nome,
        public string $banco,
        public string $ambiente,
        public string $chaveAPI,
        public string $clientIDAPI
    ){}
}