<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Agenda;

use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraCriarEvento;
use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento;

interface Agenda
{
    public function listarEventos(): array;
    public function criarEvento(EntradaFronteiraCriarEvento $parametros): string;
    public function atualizarEvento(EntradaFronteiraAtualizarEvento $parametros): void;
    public function deletarEvento(string $eventoCodigo): void;
}