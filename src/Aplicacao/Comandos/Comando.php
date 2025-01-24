<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos;

interface Comando
{
	public function executar(): void;
    public function getPayload(): array;
}
