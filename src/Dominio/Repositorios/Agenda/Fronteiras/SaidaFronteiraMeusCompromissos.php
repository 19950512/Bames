<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Agenda\Fronteiras;

final class SaidaFronteiraMeusCompromissos
{
    private array $compromissos = [];
    public function __construct(){}

    public function adicionarCompromisso(CompromissoAgenda $compromisso): void
    {
        $this->compromissos[] = $compromisso;
    }

    public function obterCompromissos(): array
    {
        return $this->compromissos;
    }
}
