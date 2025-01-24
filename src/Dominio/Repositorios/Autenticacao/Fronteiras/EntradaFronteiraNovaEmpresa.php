<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Autenticacao\Fronteiras;

final readonly class EntradaFronteiraNovaEmpresa
{
    public function __construct(
        public string $empresaCodigo,
        public string $apelido,
        public string $numeroDocumento,
        public string $responsavelEmail,
    ){}
}
