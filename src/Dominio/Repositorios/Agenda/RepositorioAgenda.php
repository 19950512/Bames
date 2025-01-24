<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Agenda;

use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraBuscarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAdicionarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraMeusCompromissos;

interface RepositorioAgenda
{
    public function adicionarEvento(EntradaFronteiraAdicionarEvento $parametros): void;
    public function buscarEventoPorCodigo(string $codigo, string $empresaCodigo): SaidaFronteiraBuscarEvento;
    public function buscarMeusCompromissos(string $usuarioCodigo, string $empresaCodigo): SaidaFronteiraMeusCompromissos;
    public function atualizarEvento(EntradaFronteiraAtualizarEvento $parametros): void;
}