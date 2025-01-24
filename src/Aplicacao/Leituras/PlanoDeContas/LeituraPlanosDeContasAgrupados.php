<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\PlanoDeContas;

use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraPlanoDeConta;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;

final readonly class LeituraPlanosDeContasAgrupados
{
    public function __construct(
        private RepositorioPlanoDeContas  $repositorioPlanoDeContas,
    ){}

    public function executar(): array
    {

        $planoDeContas = [];

        $planos = $this->repositorioPlanoDeContas->obterTodosOsPlanosDeContas()->obterPlanosDeContas();

        foreach ($planos as $plano) {

            if(is_a($plano, SaidaFronteiraPlanoDeConta::class)){

                $planoDeContas[$plano->planoDeContasCategoria][] = [
                    'codigo' => $plano->planoDeContasCodigo,
                    'nome' => $plano->planoDeContasNome,
                    'tipo' => $plano->planoDeContasTipo,
                    'categoria' => $plano->planoDeContasCategoria,
                    'descricao' => $plano->planoDeContasDescricao,
                    'codigoPlanoDeContasPai' => $plano->paiId,
                    'nivel' => $plano->planoDeContasNivel
                ];
            }
        }

        return $planoDeContas;
    }
}