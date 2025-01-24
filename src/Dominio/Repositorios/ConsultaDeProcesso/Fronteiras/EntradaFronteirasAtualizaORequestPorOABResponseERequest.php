<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras;

readonly final class EntradaFronteirasAtualizaORequestPorOABResponseERequest
{

    public function __construct(
        public string $empresaCodigo,
        public string $requestID,
        public string $descricao,
        public string $momento,
        public string $tipo = '',
        public string $status = '',
        public int $quantidadeDeProcessos = 0,
        public string $payload_request = '',
        public string $payload_response = '',
    ){}
}