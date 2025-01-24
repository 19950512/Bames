<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\Fronteiras;

final class SaidaFronteiraClientes
{

    private array $clientes = [];

    public function __construct(){}

    public function add(ClienteInformacoesBasicas $cliente): void
    {
        $this->clientes[] = $cliente;
    }

    public function toArray(): array
    {
        return $this->clientes;
    }
}
