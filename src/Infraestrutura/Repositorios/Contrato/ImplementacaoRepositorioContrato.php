<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Contrato;

use PDO;
use Override;
use Exception;
use App\Dominio\Repositorios\Contrato\RepositorioContrato;
use App\Dominio\Repositorios\Contrato\Fronteiras\SaidaFronteiraContrato;
use App\Dominio\Repositorios\Contrato\Fronteiras\EntradaFronteiraCriarContrato;

final class ImplementacaoRepositorioContrato implements RepositorioContrato
{
    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function criarContrato(EntradaFronteiraCriarContrato $parametros): void
    {

        $sql = "INSERT INTO contratos
            (contrato_id, cliente_id, conta_bancaria_id, business_id, contrato_status, contrato_recorrente, contrato_data_inicio, contrato_meio_de_pagamento, contrato_dia_vencimento, contrato_dia_emissao_cobranca, contrato_parcelas, contrato_valor, contrato_multa, contrato_juros, contrato_valor_desconto_antecipacao, contrato_tipo_desconto, contrato_tipo_juros, contrato_tipo_multa, autodata, contrato_horario_emissao_cobranca_hora, contrato_horario_emissao_cobranca_minuto)
            VALUES (:contrato_id, :cliente_id, :conta_bancaria_id, :business_id, :contrato_status, :contrato_recorrente, :contrato_data_inicio, :contrato_meio_de_pagamento, :contrato_dia_vencimento, :contrato_dia_emissao_cobranca, :contrato_parcelas, :contrato_valor, :contrato_multa, :contrato_juros, :contrato_valor_desconto_antecipacao, :contrato_tipo_desconto, :contrato_tipo_juros, :contrato_tipo_multa, :autodata, :contrato_horario_emissao_cobranca_hora, :contrato_horario_emissao_cobranca_minuto)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':contrato_id' => $parametros->codigo,
            ':cliente_id' => $parametros->clienteCodigo,
            ':conta_bancaria_id' => $parametros->contaBancariaCodigo,
            ':business_id' => $parametros->empresaCodigo,
            ':contrato_status' => $parametros->status,
            ':contrato_recorrente' => $parametros->recorrente ? 1 : 0,
            ':contrato_data_inicio' => $parametros->dataInicio,
            ':contrato_meio_de_pagamento' => $parametros->meioPagamento,
            ':contrato_dia_vencimento' => $parametros->diaVencimento,
            ':contrato_dia_emissao_cobranca' => $parametros->diaEmissaoCobranca,
            ':contrato_parcelas' => $parametros->parcela,
            ':contrato_valor' => $parametros->valor,
            ':contrato_multa' => $parametros->multa,
            ':contrato_juros' => $parametros->juros,
            ':contrato_valor_desconto_antecipacao' => $parametros->descontoAntecipacao,
            ':contrato_tipo_desconto' => $parametros->tipoDescontoAntecipacao,
            ':contrato_tipo_juros' => $parametros->tipoJuro,
            ':contrato_tipo_multa' => $parametros->tipoMulta,
            ':contrato_horario_emissao_cobranca_hora' => $parametros->horarioEmissaoCobrancaHora,
            ':contrato_horario_emissao_cobranca_minuto' => $parametros->horarioEmissaoCobrancaMinuto,
            ':autodata' => date('Y-m-d H:i:s')
        ]);
    }

    #[Override] public function buscarContratoPorCodigo(string $contratoCodigo, string $empresaCodigo): SaidaFronteiraContrato
    {
        $sql = "SELECT
                contrato_id,
                cliente_id,
                conta_bancaria_id,
                business_id,
                contrato_status,
                contrato_recorrente,
                contrato_data_inicio,
                contrato_meio_de_pagamento,
                contrato_dia_vencimento,
                contrato_dia_emissao_cobranca,
                contrato_parcelas,
                contrato_valor,
                contrato_multa,
                contrato_juros,
                contrato_valor_desconto_antecipacao,
                contrato_tipo_desconto,
                contrato_tipo_juros,
                contrato_tipo_multa,
                contrato_horario_emissao_cobranca_hora,
                contrato_horario_emissao_cobranca_minuto,
                autodata
            FROM contratos
            WHERE contrato_id = :contrato_id AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':contrato_id' => $contratoCodigo,
            ':business_id' => $empresaCodigo
        ]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!isset($contrato['contrato_id'])) {
            throw new Exception("Contrato não encontrado.");
        }

        return new SaidaFronteiraContrato(
            codigo: $contrato['contrato_id'],
            clienteCodigo: $contrato['cliente_id'],
            contaBancariaCodigo: $contrato['conta_bancaria_id'],
            empresaCodigo: $contrato['business_id'],
            status: $contrato['contrato_status'],
            horarioEmissaoCobrancaHora: $contrato['contrato_horario_emissao_cobranca_hora'],
            horarioEmissaoCobrancaMinuto: $contrato['contrato_horario_emissao_cobranca_minuto'],
            recorrente: $contrato['contrato_recorrente'],
            dataInicio: $contrato['contrato_data_inicio'],
            dataCriacao: $contrato['autodata'],
            meioPagamento: $contrato['contrato_meio_de_pagamento'],
            diaVencimento: $contrato['contrato_dia_vencimento'],
            diaEmissaoCobranca: $contrato['contrato_dia_emissao_cobranca'],
            parcela: $contrato['contrato_parcelas'],
            valor: $contrato['contrato_valor'],
            multa: $contrato['contrato_multa'],
            juros: $contrato['contrato_juros'],
            descontoAntecipacao: $contrato['contrato_valor_desconto_antecipacao'],
            tipoDescontoAntecipacao: $contrato['contrato_tipo_desconto'],
            tipoJuro: $contrato['contrato_tipo_juros'],
            tipoMulta: $contrato['contrato_tipo_multa'],
        );
    }

    #[Override] public function salvarEvento(string $contratoCodigo, string $empresaCodigo, string $evento): void
    {
        $sql = "INSERT INTO contratos_eventos
            (business_id, contrato_id, evento_momento, evento_descricao)
            VALUES (:business_id, :contrato_id, :evento_momento, :evento_descricao)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':business_id' => $empresaCodigo,
            ':contrato_id' => $contratoCodigo,
            ':evento_momento' => date('Y-m-d H:i:s'),
            ':evento_descricao' => $evento
        ]);
    }


}