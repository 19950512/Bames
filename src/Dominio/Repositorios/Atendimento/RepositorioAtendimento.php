<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Atendimento;

use App\Dominio\Repositorios\Atendimento\Fronteiras\EntradaFronteiraNovoAtendimento;
use App\Dominio\Repositorios\Atendimento\Fronteiras\SaidaFronteiraBuscarAtendimento;

interface RepositorioAtendimento
{
    public function novoAtendimento(EntradaFronteiraNovoAtendimento $parametros): void;
    public function obterAtendimento(string $empresaCodigo, string $atendimentoCodigo): SaidaFronteiraBuscarAtendimento;
}