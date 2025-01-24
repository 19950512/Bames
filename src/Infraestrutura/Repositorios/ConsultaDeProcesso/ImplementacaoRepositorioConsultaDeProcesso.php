<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\ConsultaDeProcesso;

use PDO;
use DateTime;
use Exception;
use DateInterval;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarRequestPorOAB;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteirasAtualizaORequestPorOABResponseERequest;

class ImplementacaoRepositorioConsultaDeProcesso implements RepositorioConsultaDeProcesso
{
    public function __construct(
        private PDO $pdo,
    ){}

    public function adicionarProcessoParaMonitoramento(string $processoMonitoramentoCodigo, string $processoCNJ, string $empresaCodigo): void
    {
        $agora = new DateTime();

        $query = "INSERT INTO business_processos_monitorado (
            business_processos_monitorado_codigo,
            processo_cnj,
            business_id,
            data_inicio_monitoramento,
            data_ultima_atualizacao
        ) VALUES (
            :business_processos_monitorado_codigo,
            :processo_cnj,
            :business_id,
            :data_inicio_monitoramento,
            :data_ultima_atualizacao
        )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':business_processos_monitorado_codigo' => $processoMonitoramentoCodigo,
            ':processo_cnj' => $processoCNJ,
            ':business_id' => $empresaCodigo,
            ':data_inicio_monitoramento' => $agora->format('Y-m-d H:i:s'),
            ':data_ultima_atualizacao' => $agora->format('Y-m-d H:i:s')
        ]);
    }

    public function documentoJaFoiConsultadaNosUltimosDias(string $documento): bool
    {
        $agora = new DateTime();
        $intervalo = new DateInterval('P30D'); // 30 days interval
        $agora->sub($intervalo); // Subtract the interval from the current date
        $agoraFormatted = $agora->format('Y-m-d'); // Format the date
        $query = "SELECT oab_consultada 
          FROM processos_consultados_por_oab 
          WHERE oab_consultada = :oab 
            AND momento::date > :agora";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':oab' => $documento,
            ':agora' => $agoraFormatted
        ]);

        return $stmt->rowCount() > 0;
    }

    public function OABJaFoiConsultadaNosUltimosDias(string $oab): bool
    {
        $agora = new DateTime();
        $intervalo = new DateInterval('P30D'); // 30 days interval
        $agora->sub($intervalo); // Subtract the interval from the current date
        $agoraFormatted = $agora->format('Y-m-d'); // Format the date
        $query = "SELECT oab_consultada 
          FROM processos_consultados_por_oab 
          WHERE oab_consultada = :oab 
            AND momento::date > :agora";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':oab' => $oab,
            ':agora' => $agoraFormatted
        ]);

        return $stmt->rowCount() > 0;
    }

    public function atualizaORequestPorOABParaFinalizado(string $requestID): void
    {
        $query = "UPDATE processos_consultados_por_oab SET
            request_status = 'FINISHED'
        WHERE
            consulta_oab_codigo = :consulta_oab_codigo";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':consulta_oab_codigo' => $requestID
        ]);
    }

    public function atualizaORequestPorOABResponseERequest(EntradaFronteirasAtualizaORequestPorOABResponseERequest $params): void
    {
        $query = "UPDATE processos_consultados_por_oab SET
            payload_response = :payload_response,
            request_status = :request_status
        WHERE
            consulta_oab_codigo = :consulta_oab_codigo";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':payload_response' => $params->payload_response,
            ':request_status' => $params->status,
            ':consulta_oab_codigo' => $params->requestID
        ]);
    }

    public function copiarProcessosDoDocumentoJaConsultada(string $documento, string $empresaCodigo): void
    {
        $this->copiarProcessosDeOABJaConsultada($documento, $empresaCodigo);
    }

    public function copiarProcessosDeOABJaConsultada(string $oab, string $empresaCodigo): void
    {
        $agora = new DateTime();
        $intervalo = new DateInterval('P30D'); // 30 days interval
        $agora->sub($intervalo); // Subtract the interval from the current date
        $agoraFormatted = $agora->format('Y-m-d'); // Format the date
        $query = "SELECT oab_consultada, business_id
          FROM processos_consultados_por_oab 
          WHERE oab_consultada = :oab 
            AND momento::date > :agora";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':oab' => $oab,
            ':agora' => $agoraFormatted
        ]);

        $consultasOAB = $stmt->fetch(PDO::FETCH_ASSOC);

        // // echo "Vamos copiar os processos da OAB $oab para a empresa $empresaCodigo\n";

        if(isset($consultasOAB['business_id']) and $consultasOAB['business_id'] == $empresaCodigo){
            // A consulta já foi feita por esta empresa nos últimos 30 dias
            return;
        }

        $query = "SELECT * FROM processos WHERE oab_consultada = :oab ORDER BY codigo ASC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':oab' => $oab
        ]);
        $processos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // // echo "Vamos copiar ".count($processos)." processos da OAB $oab para a empresa $empresaCodigo\n";
        foreach($processos as $processo) {

            // Vamos verificar se o processo já não existe para esta empresa
            $query = "SELECT 1 FROM processos WHERE business_id = :business_id AND processo_codigo = :processo_codigo";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':business_id' => $empresaCodigo,
                ':processo_codigo' => $processo['processo_codigo']
            ]);

            if($stmt->rowCount() > 0){
                // O processo já existe para esta empresa
                // // echo "O processo {$processo['processo_codigo']} já existe para a empresa $empresaCodigo\n";
                continue;
            }

            try {

                $query = "INSERT INTO processos (
                    business_id,
                    processo_codigo,
                    processo_numero_cnj,
                    processo_data_ultima_movimentacao,
                    processo_quantidade_movimentacoes,
                    processo_demandante,
                    processo_demandado,
                    processo_ultima_movimentacao_descricao,
                    processo_ultima_movimentacao_data,
                    oab_consultada
                ) VALUES (
                    :business_id,
                    :processo_codigo,
                    :processo_numero_cnj,
                    :processo_data_ultima_movimentacao,
                    :processo_quantidade_movimentacoes,
                    :processo_demandante,
                    :processo_demandado,
                    :processo_ultima_movimentacao_descricao,
                    :processo_ultima_movimentacao_data,
                    :oab_consultada
                )";

                $stmt = $this->pdo->prepare($query);
                $stmt->execute([
                    ':business_id' => $empresaCodigo,
                    ':processo_codigo' => $processo['processo_codigo'],
                    ':processo_numero_cnj' => $processo['processo_numero_cnj'],
                    ':processo_data_ultima_movimentacao' => $processo['processo_data_ultima_movimentacao'],
                    ':processo_quantidade_movimentacoes' => $processo['processo_quantidade_movimentacoes'],
                    ':processo_demandante' => $processo['processo_demandante'],
                    ':processo_demandado' => $processo['processo_demandado'],
                    ':processo_ultima_movimentacao_descricao' => $processo['processo_ultima_movimentacao_descricao'],
                    ':processo_ultima_movimentacao_data' => $processo['processo_ultima_movimentacao_data'],
                    ':oab_consultada' => $processo['oab_consultada']
                ]);
            }catch (Exception $erro) {
                // // echo "Erro ao inserir o processo {$processo['processo_codigo']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                continue;
            }

            // // echo "Vamos pegar as fontes do processo {$processo['processo_codigo']} para a empresa $empresaCodigo\n";

            try {

                $query = "SELECT * FROM processos_fontes WHERE processo_codigo = :processo_codigo";
                $stmt = $this->pdo->prepare($query);
                $stmt->execute([
                    ':processo_codigo' => $processo['processo_codigo']
                ]);

                $fontes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // echo "Vamos copiar ".count($fontes)." fontes do processo {$processo['processo_codigo']} para a empresa $empresaCodigo\n";
                foreach ($fontes as $fonte) {

                    try {

                        // Vamos verificar se a fonte já não existe para esta empresa
                        $query = "SELECT 1 FROM processos_fontes WHERE business_id = :business_id AND processo_codigo = :processo_codigo AND fonte_codigo = :fonte_codigo";
                        $stmt = $this->pdo->prepare($query);
                        $stmt->execute([
                            ':business_id' => $empresaCodigo,
                            ':processo_codigo' => $processo['processo_codigo'],
                            ':fonte_codigo' => $fonte['fonte_codigo']
                        ]);

                        if($stmt->rowCount() > 0){
                            // A fonte já existe para esta empresa
                           // echo "A fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} já existe para a empresa $empresaCodigo\n";
                            continue;
                        }
                    }catch (Exception $erro) {
                         //echo "Erro ao pegar as fontes do processo {$processo['processo_codigo']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                        continue;
                    }

                    try {

                        $query = "INSERT INTO processos_fontes (
                            business_id,
                            processo_codigo,
                            fonte_codigo,
                            fonte_nome,
                            fonte_descricao,
                            fonte_link,
                            fonte_tipo,
                            fonte_data_ultima_verificacao,
                            fonte_data_ultima_movimentacao,
                            fonte_segredo_justica,
                            fonte_arquivado,
                            fonte_fisico,
                            fonte_sistema,
                            fonte_quantidade_envolvidos,
                            fonte_quantidade_movimentacoes,
                            fonte_grau,
                            fonte_capa_classe,
                            fonte_capa_assunto,
                            fonte_capa_area,
                            fonte_capa_orgao_julgador,
                            fonte_capa_valor_causa,
                            fonte_capa_valor_moeda,
                            fonte_capa_data_distribuicao,
                            fonte_tribunal_id,
                            fonte_tribunal_nome,
                            fonte_tribunal_sigla,
                            fonte_informacoes_complementares
                        ) VALUES (
                            :business_id,
                            :processo_codigo,
                            :fonte_codigo,
                            :fonte_nome,
                            :fonte_descricao,
                            :fonte_link,
                            :fonte_tipo,
                            :fonte_data_ultima_verificacao,
                            :fonte_data_ultima_movimentacao,
                            :fonte_segredo_justica,
                            :fonte_arquivado,
                            :fonte_fisico,
                            :fonte_sistema,
                            :fonte_quantidade_envolvidos,
                            :fonte_quantidade_movimentacoes,
                            :fonte_grau,
                            :fonte_capa_classe,
                            :fonte_capa_assunto,
                            :fonte_capa_area,
                            :fonte_capa_orgao_julgador,
                            :fonte_capa_valor_causa,
                            :fonte_capa_valor_moeda,
                            :fonte_capa_data_distribuicao,
                            :fonte_tribunal_id,
                            :fonte_tribunal_nome,
                            :fonte_tribunal_sigla,
                            :fonte_informacoes_complementares
                        )";

                        $stmt = $this->pdo->prepare($query);

                        $stmt->execute([
                            ':business_id' => $empresaCodigo,
                            ':processo_codigo' => $processo['processo_codigo'],
                            ':fonte_codigo' => $fonte['fonte_codigo'],
                            ':fonte_nome' => $fonte['fonte_nome'],
                            ':fonte_descricao' => $fonte['fonte_descricao'],
                            ':fonte_link' => $fonte['fonte_link'],
                            ':fonte_tipo' => $fonte['fonte_tipo'],
                            ':fonte_data_ultima_verificacao' => $fonte['fonte_data_ultima_verificacao'],
                            ':fonte_data_ultima_movimentacao' => $fonte['fonte_data_ultima_movimentacao'],
                            ':fonte_segredo_justica' => $fonte['fonte_segredo_justica'] ? 'true' : 'false', // boolean
                            ':fonte_arquivado' => $fonte['fonte_arquivado'] ? 'true' : 'false',
                            ':fonte_fisico' => $fonte['fonte_fisico'] ? 'true' : 'false',
                            ':fonte_sistema' => $fonte['fonte_sistema'],
                            ':fonte_quantidade_envolvidos' => $fonte['fonte_quantidade_envolvidos'],
                            ':fonte_quantidade_movimentacoes' => $fonte['fonte_quantidade_movimentacoes'],
                            ':fonte_grau' => $fonte['fonte_grau'],
                            ':fonte_capa_classe' => $fonte['fonte_capa_classe'],
                            ':fonte_capa_assunto' => $fonte['fonte_capa_assunto'],
                            ':fonte_capa_area' => $fonte['fonte_capa_area'],
                            ':fonte_capa_orgao_julgador' => $fonte['fonte_capa_orgao_julgador'],
                            ':fonte_capa_valor_causa' => $fonte['fonte_capa_valor_causa'],
                            ':fonte_capa_valor_moeda' => $fonte['fonte_capa_valor_moeda'],
                            ':fonte_capa_data_distribuicao' => $fonte['fonte_capa_data_distribuicao'],
                            ':fonte_tribunal_id' => $fonte['fonte_tribunal_id'],
                            ':fonte_tribunal_nome' => $fonte['fonte_tribunal_nome'],
                            ':fonte_tribunal_sigla' => $fonte['fonte_tribunal_sigla'],
                            ':fonte_informacoes_complementares' => $fonte['fonte_informacoes_complementares']
                        ]);

                       //  // echo "Vamos pegar os envolvidos da fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} para a empresa $empresaCodigo\n";

                        $query = "SELECT * FROM processos_envolvidos WHERE processo_codigo = :processo_codigo AND fonte_codigo = :fonte_codigo";
                        $stmt = $this->pdo->prepare($query);
                        $stmt->execute([
                            ':processo_codigo' => $processo['processo_codigo'],
                            ':fonte_codigo' => $fonte['fonte_codigo']
                        ]);

                        $envolvidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                         //// echo "Vamos copiar ".count($envolvidos)." envolvidos da fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} para a empresa $empresaCodigo\n";

                        foreach($envolvidos as $envolvido) {

                            // Vamos verificar se o envolvido já não existe para esta empresa
                            $query = "SELECT 1 FROM processos_envolvidos WHERE business_id = :business_id AND processo_codigo = :processo_codigo AND fonte_codigo = :fonte_codigo AND envolvido_tipo = :envolvido_tipo AND envolvido_nome = :envolvido_nome AND envolvido_documento = :envolvido_documento";
                            $stmt = $this->pdo->prepare($query);
                            $stmt->execute([
                                ':business_id' => $empresaCodigo,
                                ':processo_codigo' => $processo['processo_codigo'],
                                ':fonte_codigo' => $fonte['fonte_codigo'],
                                ':envolvido_tipo' => $envolvido['envolvido_tipo'],
                                ':envolvido_nome' => $envolvido['envolvido_nome'],
                                ':envolvido_documento' => $envolvido['envolvido_documento']
                            ]);

                            if($stmt->rowCount() > 0){
                                // O envolvido já existe para esta empresa
                                 // echo "O envolvido {$envolvido['envolvido_nome']} da fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} já existe para a empresa $empresaCodigo\n";
                                continue;
                            }

                            try {

                                $query = "INSERT INTO processos_envolvidos (
                                    business_id,
                                    processo_codigo,
                                    fonte_codigo,
                                    envolvido_codigo,
                                    envolvido_oab,
                                    envolvido_tipo,
                                    envolvido_nome,
                                    envolvido_documento,
                                    envolviodo_quantidade_processos,
                                    pessoa_codigo
                                ) VALUES (
                                    :business_id,
                                    :processo_codigo,
                                    :fonte_codigo,
                                    :envolvido_codigo,
                                    :envolvido_oab,
                                    :envolvido_tipo,
                                    :envolvido_nome,
                                    :envolvido_documento,
                                    :envolviodo_quantidade_processos,
                                    :pessoa_codigo
                                )";
                                $stmt = $this->pdo->prepare($query);
                                $stmt->execute([
                                    ':business_id' => $empresaCodigo,
                                    ':processo_codigo' => $processo['processo_codigo'],
                                    ':fonte_codigo' => $fonte['fonte_codigo'],
                                    ':envolvido_oab' => $envolvido['envolvido_oab'],
                                    ':envolvido_codigo' => $envolvido['envolvido_codigo'],
                                    ':envolvido_tipo' => $envolvido['envolvido_tipo'],
                                    ':envolvido_nome' => $envolvido['envolvido_nome'],
                                    ':envolvido_documento' => $envolvido['envolvido_documento'],
                                    ':envolviodo_quantidade_processos' => $envolvido['envolviodo_quantidade_processos'],
                                    ':pessoa_codigo' => $envolvido['pessoa_codigo']
                                ]);

                                 // echo "Vamos verificar se a pessoa já existe para a empresa $empresaCodigo\n";

                                if (!empty($envolvido['envolvido_documento'])) {

                                     // echo "Vamos verificar se a pessoa já existe para a empresa $empresaCodigo\n";

                                    $sql = $this->pdo->prepare('SELECT pessoa_codigo FROM pessoas WHERE pessoa_documento = :documento AND business_id = :business_id');
                                    $sql->execute([
                                        ':documento' => $envolvido['envolvido_documento'],
                                        ':business_id' => $empresaCodigo
                                    ]);

                                    $pessoaTemp = $sql->fetch(PDO::FETCH_ASSOC);

                                    if (!isset($pessoaTemp['pessoa_codigo']) or empty($pessoaTemp['pessoa_codigo'])) {

                                         // echo "Vamos inserir a pessoa {$envolvido['envolvido_nome']} para a empresa $empresaCodigo\n";

                                        try {

                                            $query = "INSERT INTO pessoas (
                                                business_id,
                                                pessoa_codigo,
                                                pessoa_nome,
                                                pessoa_documento,
                                                pessoa_tipo
                                            ) VALUES (
                                                :business_id,
                                                :pessoa_codigo,
                                                :pessoa_nome,
                                                :pessoa_documento,
                                                :pessoa_tipo
                                            )";
                                            $stmt = $this->pdo->prepare($query);
                                            $stmt->execute([
                                                ':business_id' => $empresaCodigo,
                                                ':pessoa_codigo' => $envolvido['pessoa_codigo'],
                                                ':pessoa_nome' => $envolvido['envolvido_nome'],
                                                ':pessoa_documento' => $envolvido['envolvido_documento'],
                                                ':pessoa_tipo' => $envolvido['envolvido_tipo'],
                                            ]);
                                        }catch (Exception $erro) {
                                             // echo "Erro ao inserir a pessoa {$envolvido['envolvido_nome']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                                            continue;
                                        }
                                    }
                                }
                            }catch (Exception $erro) {
                                 // echo "Erro ao inserir o envolvido {$envolvido['envolvido_nome']} da fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                                continue;
                            }
                        }
                    }catch (Exception $erro) {
                         // echo "Erro ao inserir a fonte {$fonte['codigo']} do processo {$processo['processo_codigo']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                        continue;
                    }
                }

            }catch (Exception $erro) {
                 // echo "Erro ao pegar as fontes do processo {$processo['processo_codigo']} para a empresa $empresaCodigo: {$erro->getMessage()}\n";
                continue;
            }
        }
    }

    public function atualizarUltimaMovimentacaoDoProcesso(string $processoCodigo, string $empresaCodigo, string $ultimaMovimentacaoDescricao, string $ultimaMovimentacaoData): void
    {
        $query = "UPDATE processos SET
            processo_ultima_movimentacao_descricao = :processo_ultima_movimentacao_descricao,
            processo_ultima_movimentacao_data = :processo_ultima_movimentacao_data,
        WHERE
            processo_codigo = :processo_codigo AND business_id = :business_id";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':processo_ultima_movimentacao_descricao' => $ultimaMovimentacaoDescricao,
            ':processo_ultima_movimentacao_data' => $ultimaMovimentacaoData,
            ':processo_codigo' => $processoCodigo,
            ':business_id' => $empresaCodigo
        ]);
    }
    public function salvarRequestPorOAB(EntradaFronteiraSalvarRequestPorOAB $params): void
    {

        $query = "INSERT INTO processos_consultados_por_oab (
            consulta_oab_codigo,
            business_id,
            usuario_id,
            oab_consultada,
            momento,
            mensagem,
            payload_request,
            payload_response,
            request_status
        ) VALUES (
            :consulta_oab_codigo,
            :business_id,
            :usuario_id,
            :oab_consultada,
            :momento,
            :mensagem,
            :payload_request,
            :payload_response,
            :request_status
        )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':consulta_oab_codigo' => $params->requestID,
            ':business_id' => $params->empresaCodigo,
            ':usuario_id' => $params->contaCodigo,
            ':oab_consultada' => $params->oab,
            ':momento' => $params->momento,
            ':mensagem' => $params->descricao,
            ':payload_request' => json_encode($params, JSON_PRETTY_PRINT),
            ':payload_response' => json_encode([], JSON_PRETTY_PRINT),
            ':request_status' => 'PENDING'
        ]);
    }

    public function jaExisteUmProcessoComEsteCNJ(string $processoCNJ, string $empresaCodigo): bool
    {
        $query = "SELECT 1 FROM processos WHERE business_id = :business_id AND processo_numero_cnj = :processo_numero_cnj";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':business_id' => $empresaCodigo,
            ':processo_numero_cnj' => $processoCNJ
        ]);

        return $stmt->rowCount() > 0;
    }

    public function salvaEvento(string $requestID, string $empresaCodigo, string $evento): void
    {
        $query = "INSERT INTO processos_consultados_por_oab_eventos (
            business_id,
            consulta_oab_codigo,
            evento_momento,
            evento_descricao
        ) VALUES (
            :business_id,
            :consulta_oab_codigo,
            :momento,
            :descricao
        )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':business_id' => $empresaCodigo,
            ':consulta_oab_codigo' => $requestID,
            ':momento' => (new DateTime())->format('Y-m-d H:i:s'),
            ':descricao' => $evento
        ]);
    }

    public function salvarProcesso(EntradaFronteiraSalvarProcesso $parametros): void
    {

        $query = "SELECT 1 FROM processos WHERE business_id = :business_id AND processo_numero_cnj = :processo_numero_cnj";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':business_id' => $parametros->business_id,
            ':processo_numero_cnj' => $parametros->processo_numero_cnj
        ]);

        if($stmt->rowCount() > 0){
            return;
        }

        $query = "INSERT INTO processos (
            business_id,
            processo_codigo,
            processo_numero_cnj,
            processo_data_ultima_movimentacao,
            processo_quantidade_movimentacoes,
            processo_demandante,
            processo_demandado,
            processo_ultima_movimentacao_descricao,
            processo_ultima_movimentacao_data,
            oab_consultada
        ) VALUES (
            :business_id,
            :processo_codigo,
            :processo_numero_cnj,
            :processo_data_ultima_movimentacao,
            :processo_quantidade_movimentacoes,
            :processo_demandante,
            :processo_demandado,
            :processo_ultima_movimentacao_descricao,
            :processo_ultima_movimentacao_data,
            :oab_consultada
        )";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':business_id' => $parametros->business_id,
            ':processo_codigo' => $parametros->processo_codigo,
            ':processo_numero_cnj' => $parametros->processo_numero_cnj,
            ':processo_data_ultima_movimentacao' => $parametros->processo_data_ultima_movimentacao,
            ':processo_quantidade_movimentacoes' => $parametros->processo_quantidade_movimentacoes,
            ':processo_demandante' => $parametros->processo_demandante,
            ':processo_demandado' => $parametros->processo_demandado,
            ':processo_ultima_movimentacao_descricao' => $parametros->processo_ultima_movimentacao_descricao,
            ':processo_ultima_movimentacao_data' => $parametros->processo_ultima_movimentacao_data,
            ':oab_consultada' => $parametros->oab_ou_documento_consultada
        ]);

        foreach($parametros->fontes as $fonte){

            $query = "INSERT INTO processos_fontes (
                business_id,
                processo_codigo,
                fonte_codigo,
                fonte_nome,
                fonte_descricao,
                fonte_link,
                fonte_tipo,
                fonte_data_ultima_verificacao,
                fonte_data_ultima_movimentacao,
                fonte_segredo_justica,
                fonte_arquivado,
                fonte_fisico,
                fonte_sistema,
                fonte_quantidade_envolvidos,
                fonte_quantidade_movimentacoes,
                fonte_grau,
                fonte_capa_classe,
                fonte_capa_assunto,
                fonte_capa_area,
                fonte_capa_orgao_julgador,
                fonte_capa_valor_causa,
                fonte_capa_valor_moeda,
                fonte_capa_data_distribuicao,
                fonte_tribunal_id,
                fonte_tribunal_nome,
                fonte_tribunal_sigla,
                fonte_informacoes_complementares
            ) VALUES (
                :business_id,
                :processo_codigo,
                :fonte_codigo,
                :fonte_nome,
                :fonte_descricao,
                :fonte_link,
                :fonte_tipo,
                :fonte_data_ultima_verificacao,
                :fonte_data_ultima_movimentacao,
                :fonte_segredo_justica,
                :fonte_arquivado,
                :fonte_fisico,
                :fonte_sistema,
                :fonte_quantidade_envolvidos,
                :fonte_quantidade_movimentacoes,
                :fonte_grau,
                :fonte_capa_classe,
                :fonte_capa_assunto,
                :fonte_capa_area,
                :fonte_capa_orgao_julgador,
                :fonte_capa_valor_causa,
                :fonte_capa_valor_moeda,
                :fonte_capa_data_distribuicao,
                :fonte_tribunal_id,
                :fonte_tribunal_nome,
                :fonte_tribunal_sigla,
                :fonte_informacoes_complementares
            )";
            try {

                $stmt = $this->pdo->prepare($query);
                $stmt->execute([
                    ':business_id' => $parametros->business_id,
                    ':processo_codigo' => $parametros->processo_codigo,
                    ':fonte_codigo' => $fonte->codigo,
                    ':fonte_nome' => $fonte->nome,
                    ':fonte_descricao' => $fonte->descricao,
                    ':fonte_link' => $fonte->link,
                    ':fonte_tipo' => $fonte->tipo,
                    ':fonte_data_ultima_verificacao' => $fonte->dataUltimaVerificacao,
                    ':fonte_data_ultima_movimentacao' => $fonte->dataUltimaMovimentacao,
                    ':fonte_segredo_justica' => $fonte->segredoJustica ? 'true' : 'false',
                    ':fonte_arquivado' => $fonte->arquivado ? 'true' : 'false',
                    ':fonte_fisico' => $fonte->fisico ? 'true' : 'false',
                    ':fonte_sistema' => $fonte->sistema ? 'true' : 'false',
                    ':fonte_quantidade_envolvidos' => $fonte->quantidadeEnvolvidos,
                    ':fonte_quantidade_movimentacoes' => $fonte->quantidadeMovimentacoes,
                    ':fonte_grau' => $fonte->grau,
                    ':fonte_capa_classe' => $fonte->capaAssunto,
                    ':fonte_capa_assunto' => $fonte->capaAssunto,
                    ':fonte_capa_area' => $fonte->capaArea,
                    ':fonte_capa_orgao_julgador' => $fonte->capaOrgaoJulgador,
                    ':fonte_capa_valor_causa' => $fonte->capaValorCausa,
                    ':fonte_capa_valor_moeda' => $fonte->capaValorMoeda,
                    ':fonte_capa_data_distribuicao' => $fonte->capaDataDistribuicao,
                    ':fonte_tribunal_id' => $fonte->tribunalID,
                    ':fonte_tribunal_nome' => $fonte->tribunalNome,
                    ':fonte_tribunal_sigla' => $fonte->tribunalSigla,
                    ':fonte_informacoes_complementares' => implode(' - ', $fonte->informacoesComplementares)
                ]);

                if(is_array($fonte->envolvidos) and count($fonte->envolvidos) > 0){

                    foreach($fonte->envolvidos as $envolvido){
                        $query = "INSERT INTO processos_envolvidos (
                            business_id,
                            processo_codigo,
                            fonte_codigo,
                            envolvido_codigo,
                            envolvido_tipo,
                            envolvido_nome,
                            envolvido_documento,
                            envolvido_oab,
                            envolviodo_quantidade_processos,
                            pessoa_codigo
                        ) VALUES (
                            :business_id,
                            :processo_codigo,
                            :fonte_codigo,
                            :envolvido_codigo,
                            :envolvido_tipo,
                            :envolvido_nome,
                            :envolvido_documento,
                            :envolvido_oab,
                            :envolviodo_quantidade_processos,
                            :pessoa_codigo
                        )";
                        $stmt = $this->pdo->prepare($query);

                        $stmt->execute([
                            ':business_id' => $parametros->business_id,
                            ':processo_codigo' => $parametros->processo_codigo,
                            ':fonte_codigo' => $fonte->codigo,
                            ':envolvido_codigo' => $envolvido->codigo,
                            ':envolvido_tipo' => $envolvido->tipo,
                            ':envolvido_nome' => $envolvido->nomeCompleto,
                            ':envolvido_oab' => $envolvido->oab,
                            ':envolvido_documento' => $envolvido->documento,
                            ':envolviodo_quantidade_processos' => $envolvido->quantidadeProcessos,
                            ':pessoa_codigo' => $envolvido->codigo
                        ]);

                        if(!empty($envolvido->documento)){

                            $sql = $this->pdo->prepare('SELECT pessoa_codigo FROM pessoas WHERE pessoa_documento = :documento AND business_id = :business_id');
                            $sql->execute([
                                ':documento' => $envolvido->documento,
                                ':business_id' => $parametros->business_id
                            ]);

                            $pessoaTemp = $sql->fetch(PDO::FETCH_ASSOC);

                            if(!isset($pessoaTemp['pessoa_codigo']) OR empty($pessoaTemp['pessoa_codigo'])){

                                $query = "INSERT INTO pessoas (
                                    business_id,
                                    pessoa_codigo,
                                    pessoa_nome,
                                    pessoa_documento,
                                    pessoa_tipo
                                ) VALUES (
                                    :business_id,
                                    :pessoa_codigo,
                                    :pessoa_nome,
                                    :pessoa_documento,
                                    :pessoa_tipo
                                )";
                                $stmt = $this->pdo->prepare($query);
                                $stmt->execute([
                                    ':business_id' => $parametros->business_id,
                                    ':pessoa_codigo' => $envolvido->codigo,
                                    ':pessoa_nome' => $envolvido->nomeCompleto,
                                    ':pessoa_documento' => $envolvido->documento,
                                    ':pessoa_tipo' => $envolvido->tipo,
                                ]);
                            }
                        }
                    }
                }

            }catch (Exception $erro){
                die($erro->getMessage());
            }
        }

    }
}