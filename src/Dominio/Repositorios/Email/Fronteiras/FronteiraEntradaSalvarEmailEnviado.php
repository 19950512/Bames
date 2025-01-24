<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Email\Fronteiras;

readonly final class FronteiraEntradaSalvarEmailEnviado
{
    public function __construct(
        public string $emailCodigo,
        public string $assunto,
        public string $mensagem,
        public string $destinatarioNome,
        public string $destinatarioEmail,
        public string $empresaID,
        public string $situacao,
    ){}
}