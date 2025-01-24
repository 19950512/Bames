<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\PlanoDeContas;

use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraPlanoDeConta;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;

final readonly class LeituraPlanosDeContas
{
    public function __construct(
        private RepositorioPlanoDeContas  $repositorioPlanoDeContas,
    ){}

    public function executar(): array
    {

        return array_map(function($planoDeConta){

            if(is_a($planoDeConta, SaidaFronteiraPlanoDeConta::class)){
                return [
                    'codigo' => $planoDeConta->planoDeContasCodigo,
                    'nome' => $planoDeConta->planoDeContasNome,
                    'tipo' => $planoDeConta->planoDeContasTipo,
                    'categoria' => $planoDeConta->planoDeContasCategoria,
                    'descricao' => $planoDeConta->planoDeContasDescricao,
                    'codigoPlanoDeContasPai' => $planoDeConta->paiId,
                    'nivel' => $planoDeConta->planoDeContasNivel
                ];
            }

        }, $this->repositorioPlanoDeContas->obterTodosOsPlanosDeContas()->obterPlanosDeContas());
    }
}