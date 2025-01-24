<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos\Fronteiras;

final class EntradaFronteiraConsultarProcessoPorOAB
{
    public function __construct(
        public string $OAB,
        public string $empresaCodigo,
        public string $usuarioCodigo
    ){}
}