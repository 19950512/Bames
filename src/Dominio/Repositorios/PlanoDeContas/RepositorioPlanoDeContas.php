<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\PlanoDeContas;

use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraPlanoDeConta;
use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraTodosPlanosDeContas;

interface RepositorioPlanoDeContas
{
    public function obterTodosOsPlanosDeContas(): SaidaFronteiraTodosPlanosDeContas;
    public function buscarPlanoDeContaPorCodigo(int $codigo): SaidaFronteiraPlanoDeConta;
}