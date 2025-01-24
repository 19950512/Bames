<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras;

readonly final class EntradaFronteiraSalvarRequestPorOAB
{

    public function __construct(
        public string $empresaCodigo,
        public string $contaCodigo,
        public string $requestID,
        public string $oab,
        public string $descricao,
        public string $momento,
    ){}
}