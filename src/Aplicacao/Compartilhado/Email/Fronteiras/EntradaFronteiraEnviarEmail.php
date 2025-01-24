<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Email\Fronteiras;

readonly final class EntradaFronteiraEnviarEmail
{
    public function __construct(
        public string $destinatarioEmail,
        public string $destinatarioNome,
        public string $assunto,
        public string $mensagem
    ){}
}