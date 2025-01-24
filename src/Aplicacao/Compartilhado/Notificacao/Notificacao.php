<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Notificacao;

interface Notificacao
{
    public function enviar(string $titulo, string $mensagem, string $fcmToken): void;
}
