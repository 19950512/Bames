<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\Fronteiras;

final class ClienteInformacoesBasicas
{
    public function __construct(
        public string $codigo,
        public string $nomeCompleto,
        public string $documento,
        public string $whatsapp,
    ){}
}