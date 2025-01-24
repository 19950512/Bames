<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca\Fronteiras;

use App\Aplicacao\Compartilhado\Cobranca\Enumerados\CobrancaSituacao;

readonly final class SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma
{
    public function __construct(
        public CobrancaSituacao $status,
        public string $nossoNumero,
        public string $codigoDeBarras,
        public string $linhaDigitavel,
        public string $requestPayload,
        public string $responsePayload,
    ){}

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'nossoNumero' => $this->nossoNumero,
            'codigoDeBarras' => $this->codigoDeBarras,
            'linhaDigitavel' => $this->linhaDigitavel,
            'requestPayload' => $this->requestPayload,
            'responsePayload' => $this->responsePayload,
        ];
    }
}