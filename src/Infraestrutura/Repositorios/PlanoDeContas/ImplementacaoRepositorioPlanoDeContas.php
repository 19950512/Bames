<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\PlanoDeContas;

use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraPlanoDeConta;
use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraTodosPlanosDeContas;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use Exception;
use Override;
use PDO;

final class ImplementacaoRepositorioPlanoDeContas implements RepositorioPlanoDeContas
{

    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function buscarPlanoDeContaPorCodigo(int $codigo): SaidaFronteiraPlanoDeConta
    {

        $sql = "SELECT
                codigo,
                plano_de_contas_codigo,
                plano_de_contas_nome,
                plano_de_contas_descricao,
                plano_de_contas_tipo,
                plano_de_contas_categoria,
                plano_de_contas_nivel,
                pai_id
            FROM plano_de_contas
            WHERE codigo = :codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        $stmt->execute();
        $planoDeContas = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!isset($planoDeContas['codigo'])){
            throw new Exception("Plano de contas não encontrado.");
        }

        return new SaidaFronteiraPlanoDeConta(
            planoDeContasCodigo: (int) $planoDeContas['codigo'],
            planoDeContasNome: $planoDeContas['plano_de_contas_nome'],
            planoDeContasDescricao: $planoDeContas['plano_de_contas_descricao'],
            planoDeContasTipo: $planoDeContas['plano_de_contas_tipo'],
            planoDeContasCategoria: $planoDeContas['plano_de_contas_categoria'],
            planoDeContasNivel: $planoDeContas['plano_de_contas_nivel'],
            paiId: (int) $planoDeContas['pai_id'] ?? 0
        );
    }

    #[Override] public function obterTodosOsPlanosDeContas(): SaidaFronteiraTodosPlanosDeContas
    {

        $sql = "SELECT
                codigo,
                plano_de_contas_codigo,
                plano_de_contas_nome,
                plano_de_contas_descricao,
                plano_de_contas_tipo,
                plano_de_contas_categoria,
                plano_de_contas_nivel,
                pai_id
            FROM plano_de_contas";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $planosDeContas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $saidaFronteiraTodosPlanosDeContas = new SaidaFronteiraTodosPlanosDeContas();

        foreach ($planosDeContas as $planoDeContas) {

            $saidaFronteiraTodosPlanosDeContas->adicionarPlanoDeConta(
                new SaidaFronteiraPlanoDeConta(
                    planoDeContasCodigo: (int) $planoDeContas['codigo'],
                    planoDeContasNome: $planoDeContas['plano_de_contas_nome'],
                    planoDeContasDescricao: $planoDeContas['plano_de_contas_descricao'],
                    planoDeContasTipo: $planoDeContas['plano_de_contas_tipo'],
                    planoDeContasCategoria: $planoDeContas['plano_de_contas_categoria'],
                    planoDeContasNivel: $planoDeContas['plano_de_contas_nivel'],
                    paiId: (int) $planoDeContas['pai_id'] ?? 0
                )
            );
        }

        return $saidaFronteiraTodosPlanosDeContas;
    }
}
