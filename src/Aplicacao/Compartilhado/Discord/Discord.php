<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Discord;

use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;

interface Discord
{
    public function enviar(CanalDeTexto $canaldeTexto, string $mensagem): void;
}
