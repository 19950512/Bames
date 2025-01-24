<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras;

readonly final class EntradaFronteiraSalvarRequestPorDocumento
{
    public function __construct(
        public string $empresaCodigo,
        public string $contaCodigo,
        public string $requestID,
        public string $descricao,
        public string $momento,
    ){}
}