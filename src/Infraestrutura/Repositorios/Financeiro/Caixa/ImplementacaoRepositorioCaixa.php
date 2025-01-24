<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Financeiro\Caixa;

use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\FronteiraEntradaLancarMovimentacaoNoCaixa;
use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\FronteiraSaidaMovimentacoes;
use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\Movimentacao;
use App\Dominio\Repositorios\Financeiro\Caixa\RepositorioCaixa;
use Exception;
use Override;
use PDO;

readonly final class ImplementacaoRepositorioCaixa implements RepositorioCaixa
{

    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function obterTodasAsMovimentacoesDaConta(string $empresaCodigo, string $contaBancariaCodigo): FronteiraSaidaMovimentacoes
    {

        $sql = "SELECT
                    caixa_movimentacao_codigo,
                    plano_de_contas_codigo,
                    valor,
                    descricao,
                    plano_de_contas_nome,
                    pagador_codigo,
                    pagador_documento,
                    pagador_nome_completo,
                    cobranca_codigo,
                    boleto_codigo,
                    created_at
                FROM caixa_movimentacoes
                WHERE business_id = :business_id AND conta_bancaria_id = :conta_bancaria_id
                ORDER BY created_at DESC";

        try {

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'business_id' => $empresaCodigo,
                'conta_bancaria_id' => $contaBancariaCodigo
            ]);

        }catch (Exception $erro) {

            throw new Exception("Ops, não foi possível obter as movimentações da conta. " . $erro->getMessage());
        }

        $fronteiraSaidaMovimentacoes = new FronteiraSaidaMovimentacoes();

        while ($movimentacao = $stmt->fetch()) {
            $fronteiraSaidaMovimentacoes->adicionarMovimentacao(
                new Movimentacao(
                    planoDeContaCodigo: (int) $movimentacao['plano_de_contas_codigo'],
                    planoDeContaNome: (string) $movimentacao['plano_de_contas_nome'] ?? '',
                    codigoMovimentacao: (string) $movimentacao['caixa_movimentacao_codigo'],
                    valor: (float) $movimentacao['valor'],
                    descricao: (string) $movimentacao['descricao'],
                    dataMovimentacao: (string) $movimentacao['created_at'],
                    pagadorCodigo: (string) $movimentacao['pagador_codigo'] ?? '',
                    pagadorDocumento: (string) $movimentacao['pagador_documento'] ?? '',
                    pagadorNomeCompleto: (string) $movimentacao['pagador_nome_completo'] ?? '',
                    cobrancaCodigo: (string) $movimentacao['cobranca_codigo'] ?? '',
                    boletoCodigo: (string) $movimentacao['boleto_codigo'] ?? ''
                )
            );
        }

        return $fronteiraSaidaMovimentacoes;
    }


    #[Override] public function lancarMovimentacaoNoCaixa(FronteiraEntradaLancarMovimentacaoNoCaixa $parametros): void
    {

        $saldoAnterior = $this->calcularSaldoAtual(
            empresaCodigo: $parametros->empresaCodigo,
            contaBancariaCodigo: $parametros->contaBancariaCodigo
        );

        $saldoAtual = $saldoAnterior + $parametros->valor;

        $colunas = [
            'usuario_codigo_criou', 'business_id', 'pagador_codigo', 'pagador_nome_completo',
            'plano_de_contas_codigo', 'plano_de_contas_nome', 'boleto_codigo', 'cobranca_codigo',
            'descricao', 'valor', 'conta_bancaria_id', 'saldo_anterior', 'saldo_atual',
            'created_at', 'data_ultima_alteracao', 'caixa_movimentacao_codigo'
        ];

        $valores = [
            $parametros->usuarioCodigo ?? null,
            $parametros->empresaCodigo ?? null,
            $parametros->pagadorCodigo ?? null,
            $parametros->pagadorNomeCompleto ?? '',
            $parametros->planoDeContaCodigo ?? null,
            $parametros->planoDeContaNome ?? '',
            $parametros->boletoCodigo ?? null,
            $parametros->cobrancaCodigo ?? null,
            $parametros->descricao ?? '',
            $parametros->valor ?? 0.0,
            $parametros->contaBancariaCodigo ?? null,
            $saldoAnterior ?? 0.0,
            $saldoAtual ?? 0.0,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            $parametros->movimentacaoCodigo ?? null
        ];

        $sql = "INSERT INTO caixa_movimentacoes (
            usuario_codigo_criou,
            business_id,
            pagador_codigo,
            pagador_nome_completo,
            plano_de_contas_codigo,
            plano_de_contas_nome,
            boleto_codigo,
            cobranca_codigo,
            descricao,
            valor,
            conta_bancaria_id,
            saldo_anterior,
            saldo_atual,
            created_at,
            data_ultima_alteracao,
            caixa_movimentacao_codigo
        ) VALUES (
            :usuario_codigo_criou,
            :business_id,
            :pagador_codigo,
            :pagador_nome_completo,
            :plano_de_contas_codigo,
            :plano_de_contas_nome,
            :boleto_codigo,
            :cobranca_codigo,
            :descricao,
            :valor,
            :conta_bancaria_id,
            :saldo_anterior,
            :saldo_atual,
            :created_at,
            :data_ultima_alteracao,
            :caixa_movimentacao_codigo
        )";

        try {

            $stmt = $this->pdo->prepare($sql);

            $valores = [
                ':usuario_codigo_criou'         => $parametros->usuarioCodigo ?? null,
                ':business_id'                  => $parametros->empresaCodigo ?? null,
                ':pagador_codigo'               => $parametros->pagadorCodigo ?? null,
                ':pagador_nome_completo'        => $parametros->pagadorNomeCompleto ?? '',
                ':plano_de_contas_codigo'       => $parametros->planoDeContaCodigo ?? null,
                ':plano_de_contas_nome'         => $parametros->planoDeContaNome ?? '',
                ':boleto_codigo'                => $parametros->boletoCodigo ?? null,
                ':cobranca_codigo'              => $parametros->cobrancaCodigo ?? null,
                ':descricao'                    => $parametros->descricao ?? '',
                ':valor'                        => $parametros->valor ?? 0.0,
                ':conta_bancaria_id'           => $parametros->contaBancariaCodigo ?? null,
                ':saldo_anterior'               => $saldoAnterior ?? 0.0,
                ':saldo_atual'                  => $saldoAtual ?? 0.0,
                ':created_at'                   => date('Y-m-d H:i:s'),
                ':data_ultima_alteracao'        => date('Y-m-d H:i:s'),
                ':caixa_movimentacao_codigo'    => $parametros->movimentacaoCodigo ?? null
            ];

            $stmt->execute($valores);

        } catch (Exception $erro) {
            throw new Exception("Ops, não foi possível lançar a movimentação no caixa. " . $erro->getMessage());
        }
    }

    #[Override] public function salvarEvento(string $contaBancariaCodigo, string $movimentacaoCodigo, string $descricao, string $empresaCodigo): void
    {
        $sql = "INSERT INTO caixa_movimentacoes_eventos (business_id, caixa_movimentacao_codigo, conta_bancaria_id, evento_descricao, evento_momento) VALUES (:business_id, :caixa_movimentacao_codigo, :conta_bancaria_id, :evento_descricao, :evento_momento)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'caixa_movimentacao_codigo' => $movimentacaoCodigo,
            'conta_bancaria_id' => $contaBancariaCodigo,
            'evento_descricao' => $descricao,
            'evento_momento' => date('Y-m-d H:i:s')
        ]);
    }

    private function calcularSaldoAtual(string $empresaCodigo, string $contaBancariaCodigo): float
    {
        $sql = "SELECT SUM(valor) FROM caixa_movimentacoes WHERE business_id = :business_id AND conta_bancaria_id = :conta_bancaria_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_id' => $contaBancariaCodigo
        ]);

        return (float) $stmt->fetchColumn();
    }
}