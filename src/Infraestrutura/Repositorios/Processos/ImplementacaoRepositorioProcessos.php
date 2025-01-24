<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Processos;

use App\Dominio\Repositorios\Processos\Fronteiras\EntradaFronteiraSincronizarMovimentacoesDoProcesso;
use App\Dominio\Repositorios\Processos\Fronteiras\EnvolvidoData;
use App\Dominio\Repositorios\Processos\Fronteiras\MovimentacaoData;
use App\Dominio\Repositorios\Processos\Fronteiras\ProcessoListagem;
use App\Dominio\Repositorios\Processos\Fronteiras\SaidaFronteiraProcessoDetalhes;
use App\Dominio\Repositorios\Processos\Fronteiras\SaidaFronteiraProcessos;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;
use Override;
use PDO;

class ImplementacaoRepositorioProcessos implements RepositorioProcessos
{

    const QUERY_PROCESSO_LISTAGEM = 'SELECT
            processo_codigo,
            processo_numero_cnj,
            processo_data_ultima_movimentacao,
            processo_quantidade_movimentacoes,
            processo_demandante,
            processo_demandado,
            processo_ultima_movimentacao_descricao,
            processo_ultima_movimentacao_data
        FROM processos';

    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function atualizarDataUltimaMovimentacaoDoProcesso(string $processoCodigo, string $dataUltimaMovimentacao): void
    {
        $pdo = $this->pdo->prepare('UPDATE processos
            SET
                processo_data_ultima_movimentacao = :processo_data_ultima_movimentacao
            WHERE processo_codigo = :processo_codigo
        ');

        $pdo->execute([
            'processo_data_ultima_movimentacao' => $dataUltimaMovimentacao,
            'processo_codigo' => $processoCodigo
        ]);
    }

    #[Override] public function atualizarTotalMovimentacoesDoProcesso(string $processoCodigo, int $totalMovimentacoes): void
    {
        $pdo = $this->pdo->prepare('UPDATE processos
            SET
                processo_quantidade_movimentacoes = :processo_quantidade_movimentacoes
            WHERE processo_codigo = :processo_codigo
        ');

        $pdo->execute([
            'processo_quantidade_movimentacoes' => $totalMovimentacoes,
            'processo_codigo' => $processoCodigo
        ]);
    }
    #[Override] public function atualizarMovimentacaoDoProcesso(MovimentacaoData $parametros): void
    {
        $pdo = $this->pdo->prepare('UPDATE processos_movimentacoes
            SET
                movimentacao_codigo = :movimentacao_codigo,
                movimentacao_id_plataforma = :movimentacao_id_plataforma,
                movimentacao_data = :movimentacao_data,
                movimentacao_tipo = :movimentacao_tipo,
                movimentacao_tipo_publicacao = :movimentacao_tipo_publicacao,
                movimentacao_classificacao_predita_nome = :movimentacao_classificacao_predita_nome,
                movimentacao_classificacao_predita_descricao = :movimentacao_classificacao_predita_descricao,
                movimentacao_classificacao_predita_hierarquia = :movimentacao_classificacao_predita_hierarquia,
                movimentacao_conteudo = :movimentacao_conteudo,
                movimentacao_texto_categoria = :movimentacao_texto_categoria,
                movimentacao_fonte_processo_fonte_id = :movimentacao_fonte_processo_fonte_id,
                movimentacao_fonte_fonte_id = :movimentacao_fonte_fonte_id,
                movimentacao_fonte_nome = :movimentacao_fonte_nome,
                movimentacao_fonte_tipo = :movimentacao_fonte_tipo,
                movimentacao_fonte_sigla = :movimentacao_fonte_sigla,
                movimentacao_fonte_grau = :movimentacao_fonte_grau,
                movimentacao_fonte_grau_formatado = :movimentacao_fonte_grau_formatado,
                processo_codigo = :processo_codigo
            WHERE movimentacao_id_plataforma = :movimentacao_id_plataforma AND processo_codigo = :processo_codigo
        ');

        $pdo->execute([
            'movimentacao_codigo' => $parametros->processoCodigo,
            'movimentacao_id_plataforma' => $parametros->id,
            'movimentacao_data' => $parametros->data,
            'movimentacao_tipo' => $parametros->tipo,
            'movimentacao_tipo_publicacao' => $parametros->tipoPublicacao,
            'movimentacao_classificacao_predita_nome' => $parametros->classificacaoPreditaNome,
            'movimentacao_classificacao_predita_descricao' => $parametros->classificacaoPreditaDescricao,
            'movimentacao_classificacao_predita_hierarquia' => $parametros->classificacaoPreditaHierarquia,
            'movimentacao_conteudo' => $parametros->conteudo,
            'movimentacao_texto_categoria' => $parametros->textoCategoria,
            'movimentacao_fonte_processo_fonte_id' => $parametros->fonteProcessoFonteId,
            'movimentacao_fonte_fonte_id' => $parametros->fonteFonteId,
            'movimentacao_fonte_nome' => $parametros->fonteNome,
            'movimentacao_fonte_tipo' => $parametros->fonteTipo,
            'movimentacao_fonte_sigla' => $parametros->fonteSigla,
            'movimentacao_fonte_grau' => $parametros->fonteGrau,
            'movimentacao_fonte_grau_formatado' => $parametros->fonteGrauFormatado,
            'processo_codigo' => $parametros->processoCodigo
        ]);
    }

    #[Override] public function salvarMovimentacaoDoProcesso(MovimentacaoData $parametros): void
    {
        $pdo = $this->pdo->prepare('INSERT INTO processos_movimentacoes
            (
                business_id,
                movimentacao_codigo,
                movimentacao_id_plataforma,
                movimentacao_data,
                movimentacao_tipo,
                movimentacao_tipo_publicacao,
                movimentacao_classificacao_predita_nome,
                movimentacao_classificacao_predita_descricao,
                movimentacao_classificacao_predita_hierarquia,
                movimentacao_conteudo,
                movimentacao_texto_categoria,
                movimentacao_fonte_processo_fonte_id,
                movimentacao_fonte_fonte_id,
                movimentacao_fonte_nome,
                movimentacao_fonte_tipo,
                movimentacao_fonte_sigla,
                movimentacao_fonte_grau,
                movimentacao_fonte_grau_formatado,
                processo_codigo
            ) VALUES (
                :business_id,
                :movimentacao_codigo,
                :movimentacao_id_plataforma,
                :movimentacao_data,
                :movimentacao_tipo,
                :movimentacao_tipo_publicacao,
                :movimentacao_classificacao_predita_nome,
                :movimentacao_classificacao_predita_descricao,
                :movimentacao_classificacao_predita_hierarquia,
                :movimentacao_conteudo,
                :movimentacao_texto_categoria,
                :movimentacao_fonte_processo_fonte_id,
                :movimentacao_fonte_fonte_id,
                :movimentacao_fonte_nome,
                :movimentacao_fonte_tipo,
                :movimentacao_fonte_sigla,
                :movimentacao_fonte_grau,
                :movimentacao_fonte_grau_formatado,
                :processo_codigo
            )
        ');

        $pdo->execute([
            'business_id' => $parametros->empresaCodigo,
            'movimentacao_codigo' => $parametros->processoCodigo,
            'movimentacao_id_plataforma' => $parametros->id,
            'movimentacao_data' => $parametros->data,
            'movimentacao_tipo' => $parametros->tipo,
            'movimentacao_tipo_publicacao' => $parametros->tipoPublicacao,
            'movimentacao_classificacao_predita_nome' => $parametros->classificacaoPreditaNome,
            'movimentacao_classificacao_predita_descricao' => $parametros->classificacaoPreditaDescricao,
            'movimentacao_classificacao_predita_hierarquia' => $parametros->classificacaoPreditaHierarquia,
            'movimentacao_conteudo' => $parametros->conteudo,
            'movimentacao_texto_categoria' => $parametros->textoCategoria,
            'movimentacao_fonte_processo_fonte_id' => $parametros->fonteProcessoFonteId,
            'movimentacao_fonte_fonte_id' => $parametros->fonteFonteId,
            'movimentacao_fonte_nome' => $parametros->fonteNome,
            'movimentacao_fonte_tipo' => $parametros->fonteTipo,
            'movimentacao_fonte_sigla' => $parametros->fonteSigla,
            'movimentacao_fonte_grau' => $parametros->fonteGrau,
            'movimentacao_fonte_grau_formatado' => $parametros->fonteGrauFormatado,
            'processo_codigo' => $parametros->processoCodigo
        ]);
    }

    #[Override] public function obterProcessosDoClientePorDocumento(string $empresaCodigo, string $documento): SaidaFronteiraProcessos
    {

        $pdo = $this->pdo->prepare(self::QUERY_PROCESSO_LISTAGEM. '
            WHERE business_id = :business_id
            AND oab_consultada = :documento
            ORDER BY processo_data_ultima_movimentacao DESC
        ');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'documento' => $documento
        ]);
        $processos = $pdo->fetchAll(PDO::FETCH_ASSOC);

        return $this->_processaDadosDosProcessosListagem($processos, $empresaCodigo);
    }

    private function _processaDadosDosProcessosListagem(array $processos, string $empresaCodigo): SaidaFronteiraProcessos
    {

        $saidaFronteiraProcessos = new SaidaFronteiraProcessos();

        foreach($processos as $processoTemp){

            $processo_ultima_movimentacao_data = '';
            if(isset($processoTemp['processo_ultima_movimentacao_data']) and !empty($processoTemp['processo_ultima_movimentacao_data']) and $processoTemp['processo_ultima_movimentacao_data'] != ' '){
                $processo_ultima_movimentacao_data = date('d/m/Y', strtotime($processoTemp['processo_ultima_movimentacao_data']));
            }

            $processo = new ProcessoListagem(
                codigo: (string) $processoTemp['processo_codigo'],
                numeroCNJ: (string) $processoTemp['processo_numero_cnj'],
                dataUltimaMovimentacao: date('d/m/Y', strtotime((string) $processoTemp['processo_data_ultima_movimentacao'] ?? '')),
                quantidadeMovimentacoes: (int) $processoTemp['processo_quantidade_movimentacoes'],
                demandante: (string) $processoTemp['processo_demandante'] ?? '',
                demandado: (string) $processoTemp['processo_demandado'] ?? '',
                ultimaMovimentacaoData: $processo_ultima_movimentacao_data,
                ultimaMovimentacaoDescricao: $processoTemp['processo_ultima_movimentacao_descricao'] ?? '',
            );

            /*$pdoEnvolvidos = $this->pdo->prepare('SELECT
                    envolvido_codigo,
                    envolvido_nome,
                    envolvido_oab,
                    envolviodo_quantidade_processos,
                    envolvido_documento,
                    envolvido_tipo
                FROM processos_envolvidos
                WHERE processo_codigo = :processo_codigo
                AND business_id = :business_id');
            $pdoEnvolvidos->execute([
                'processo_codigo' => $processo->codigo,
                'business_id' => $empresaCodigo
            ]);

            foreach($pdoEnvolvidos->fetchAll(PDO::FETCH_ASSOC) as $envolvido){
                $envolvidoTemp = new EnvolvidoData(
                    codigo: (string) $envolvido['envolvido_codigo'],
                    oab: (string) $envolvido['envolvido_oab'],
                    tipo: (string) $envolvido['envolvido_tipo'],
                    nomeCompleto: (string) $envolvido['envolvido_nome'],
                    documento: (string) $envolvido['envolvido_documento'],
                    quantidadeDeProcessos: (int) $envolvido['envolviodo_quantidade_processos']
                );

                //$processo->adicionarEnvolvido($envolvidoTemp);
            }*/

            /*$pdoMovimentacoes = $this->pdo->prepare("
                SELECT
                    movimentacao_codigo,
                    movimentacao_id_plataforma,
                    movimentacao_data,
                    movimentacao_tipo,
                    movimentacao_tipo_publicacao,
                    movimentacao_classificacao_predita_nome,
                    movimentacao_classificacao_predita_descricao,
                    movimentacao_classificacao_predita_hierarquia,
                    movimentacao_conteudo,
                    movimentacao_texto_categoria,
                    movimentacao_fonte_processo_fonte_id,
                    movimentacao_fonte_fonte_id,
                    movimentacao_fonte_nome,
                    movimentacao_fonte_tipo,
                    movimentacao_fonte_sigla,
                    movimentacao_fonte_grau,
                    movimentacao_fonte_grau_formatado
                FROM processos_movimentacoes
                WHERE processo_codigo = :processo_codigo
                AND business_id = :business_id
            ");
            $pdoMovimentacoes->execute([
                'processo_codigo' => $processo->codigo,
                'business_id' => $empresaCodigo
            ]);

            foreach($pdoMovimentacoes->fetchAll(PDO::FETCH_ASSOC) as $movimentacao){

                $movimentacaoData = new MovimentacaoData(
                    id: (string) $movimentacao['movimentacao_codigo'],
                    empresaCodigo: $empresaCodigo,
                    processoCodigo: $processo->codigo,
                    processoCNJ: $processo->numeroCNJ,
                    data: (string) $movimentacao['movimentacao_data'],
                    tipo: (string) $movimentacao['movimentacao_tipo'],
                    tipoPublicacao: (string) $movimentacao['movimentacao_tipo_publicacao'],
                    classificacaoPreditaNome: (string) $movimentacao['movimentacao_classificacao_predita_nome'],
                    classificacaoPreditaDescricao: (string) $movimentacao['movimentacao_classificacao_predita_descricao'],
                    classificacaoPreditaHierarquia: (string) $movimentacao['movimentacao_classificacao_predita_hierarquia'],
                    conteudo: (string) $movimentacao['movimentacao_conteudo'],
                    textoCategoria: (string) $movimentacao['movimentacao_texto_categoria'],
                    fonteProcessoFonteId: (string) $movimentacao['movimentacao_fonte_processo_fonte_id'],
                    fonteFonteId: (string) $movimentacao['movimentacao_fonte_fonte_id'],
                    fonteNome: (string) $movimentacao['movimentacao_fonte_nome'],
                    fonteTipo: (string) $movimentacao['movimentacao_fonte_tipo'],
                    fonteSigla: (string) $movimentacao['movimentacao_fonte_sigla'],
                    fonteGrau: (string) $movimentacao['movimentacao_fonte_grau'],
                    fonteGrauFormatado: (string) $movimentacao['movimentacao_fonte_grau_formatado'],
                );
                $processo->adicionarMovimentacao($movimentacaoData);
            }*/

            $saidaFronteiraProcessos->add($processo);
        }

        return $saidaFronteiraProcessos;
    }

    #[Override] public function obterDetalhesDoProcesso(string $empresaCodigo, string $processoCodigo): SaidaFronteiraProcessoDetalhes
    {

        $pdo = $this->pdo->prepare(self::QUERY_PROCESSO_LISTAGEM. '
        WHERE business_id = :business_id
        AND processo_codigo = :processo_codigo');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'processo_codigo' => $processoCodigo
        ]);
        $processo = $pdo->fetch(PDO::FETCH_ASSOC);

        $processo_ultima_movimentacao_data = '';
        if(isset($processo['processo_ultima_movimentacao_data']) and !empty($processo['processo_ultima_movimentacao_data']) and $processo['processo_ultima_movimentacao_data'] != ' '){
            $processo_ultima_movimentacao_data = date('d/m/Y', strtotime($processo['processo_ultima_movimentacao_data']));
        }

        $saidaFronteiraProcessoDetalhes = new SaidaFronteiraProcessoDetalhes(
            codigo: (string) $processoCodigo,
            numeroCNJ: (string) $processo['processo_numero_cnj'],
            dataUltimaMovimentacao: date('d/m/Y', strtotime((string) $processo['processo_data_ultima_movimentacao'] ?? '')),
            quantidadeMovimentacoes: (int) $processo['processo_quantidade_movimentacoes'],
            demandante: (string) $processo['processo_demandante'] ?? '',
            demandado: (string) $processo['processo_demandado'] ?? '',
            ultimaMovimentacaoData: $processo_ultima_movimentacao_data,
            ultimaMovimentacaoDescricao: $processo['processo_ultima_movimentacao_descricao'] ?? '',
        );

        $pdoEnvolvidos = $this->pdo->prepare('SELECT
                envolvido_codigo,
                envolvido_nome,
                envolvido_oab,
                envolviodo_quantidade_processos,
                envolvido_documento,
                envolvido_tipo
            FROM processos_envolvidos
            WHERE processo_codigo = :processo_codigo AND business_id = :business_id');
        $pdoEnvolvidos->execute([
            'processo_codigo' => $processoCodigo,
            'business_id' => $empresaCodigo
        ]);

        foreach($pdoEnvolvidos->fetchAll(PDO::FETCH_ASSOC) as $envolvido){
            $envolvidoTemp = new EnvolvidoData(
                codigo: (string) $envolvido['envolvido_codigo'],
                oab: (string) $envolvido['envolvido_oab'],
                tipo: (string) $envolvido['envolvido_tipo'],
                nomeCompleto: (string) $envolvido['envolvido_nome'],
                documento: (string) $envolvido['envolvido_documento'],
                quantidadeDeProcessos: (int) $envolvido['envolviodo_quantidade_processos']
            );

            $saidaFronteiraProcessoDetalhes->addEnvolvido($envolvidoTemp);
        }

        // Vamos pegar as movimentações do processo.
        $pdoMovimentacoes = $this->pdo->prepare("
            SELECT
                movimentacao_codigo,
                movimentacao_id_plataforma,
                movimentacao_data,
                movimentacao_tipo,
                movimentacao_tipo_publicacao,
                movimentacao_classificacao_predita_nome,
                movimentacao_classificacao_predita_descricao,
                movimentacao_classificacao_predita_hierarquia,
                movimentacao_conteudo,
                movimentacao_texto_categoria,
                movimentacao_fonte_processo_fonte_id,
                movimentacao_fonte_fonte_id,
                movimentacao_fonte_nome,
                movimentacao_fonte_tipo,
                movimentacao_fonte_sigla,
                movimentacao_fonte_grau,
                movimentacao_fonte_grau_formatado
            FROM processos_movimentacoes
            WHERE processo_codigo = :processo_codigo
            AND business_id = :business_id ORDER BY movimentacao_data DESC");
        $pdoMovimentacoes->execute([
            'processo_codigo' => $processoCodigo,
            'business_id' => $empresaCodigo
        ]);

        foreach($pdoMovimentacoes->fetchAll(PDO::FETCH_ASSOC) as $movimentacao){

            $movimentacaoData = new MovimentacaoData(
                id: (string) $movimentacao['movimentacao_codigo'],
                empresaCodigo: $empresaCodigo,
                processoCodigo: $processoCodigo,
                processoCNJ: $saidaFronteiraProcessoDetalhes->numeroCNJ,
                data: (string) $movimentacao['movimentacao_data'],
                tipo: (string) $movimentacao['movimentacao_tipo'],
                tipoPublicacao: (string) $movimentacao['movimentacao_tipo_publicacao'],
                classificacaoPreditaNome: (string) $movimentacao['movimentacao_classificacao_predita_nome'],
                classificacaoPreditaDescricao: (string) $movimentacao['movimentacao_classificacao_predita_descricao'],
                classificacaoPreditaHierarquia: (string) $movimentacao['movimentacao_classificacao_predita_hierarquia'],
                conteudo: (string) $movimentacao['movimentacao_conteudo'],
                textoCategoria: (string) $movimentacao['movimentacao_texto_categoria'],
                fonteProcessoFonteId: (string) $movimentacao['movimentacao_fonte_processo_fonte_id'],
                fonteFonteId: (string) $movimentacao['movimentacao_fonte_fonte_id'],
                fonteNome: (string) $movimentacao['movimentacao_fonte_nome'],
                fonteTipo: (string) $movimentacao['movimentacao_fonte_tipo'],
                fonteSigla: (string) $movimentacao['movimentacao_fonte_sigla'],
                fonteGrau: (string) $movimentacao['movimentacao_fonte_grau'],
                fonteGrauFormatado: (string) $movimentacao['movimentacao_fonte_grau_formatado'],
            );
            $saidaFronteiraProcessoDetalhes->addMovimentacao($movimentacaoData);
        }


        return $saidaFronteiraProcessoDetalhes;
    }

    #[Override] public function obterProcessosDaOAB(string $empresaCodigo, string $oab): SaidaFronteiraProcessos
    {

        $pdo = $this->pdo->prepare(self::QUERY_PROCESSO_LISTAGEM. '
            WHERE business_id = :business_id
            AND oab_consultada = :oab
            ORDER BY processo_data_ultima_movimentacao DESC
        ');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'oab' => $oab
        ]);
        $processos = $pdo->fetchAll(PDO::FETCH_ASSOC);

        return $this->_processaDadosDosProcessosListagem($processos, $empresaCodigo);
    }

    #[Override] public function getTodosProcessos(string $empresaCodigo): SaidaFronteiraProcessos
    {

        $pdo = $this->pdo->prepare(self::QUERY_PROCESSO_LISTAGEM. '
            WHERE business_id = :business_id
            ORDER BY processo_data_ultima_movimentacao DESC
        ');
        $pdo->execute([
            'business_id' => $empresaCodigo
        ]);
        $processos = $pdo->fetchAll(PDO::FETCH_ASSOC);

        return $this->_processaDadosDosProcessosListagem($processos, $empresaCodigo);
    }
    #[Override] public function movimentacaoNaoExisteAinda(string $codigoMovimentacaoNaPlataforma, string $empresaCodigo): bool
    {
       $pdo = $this->pdo->prepare('SELECT
                movimentacao_id_plataforma
            FROM processos_movimentacoes
            WHERE movimentacao_id_plataforma = :movimentacao_id_plataforma and business_id = :empresaCodigo
        ');

        $pdo->execute([
            'empresaCodigo' => $empresaCodigo,
            'movimentacao_id_plataforma' => $codigoMovimentacaoNaPlataforma
        ]);
        $movimentacao = $pdo->fetch(PDO::FETCH_ASSOC);

        return !isset($movimentacao['movimentacao_id_plataforma']) or empty($movimentacao['movimentacao_id_plataforma']);
    }
    #[Override] public function sincronizarMovimentacoesDoProcesso(EntradaFronteiraSincronizarMovimentacoesDoProcesso $parametros): void
    {
        $pdo = $this->pdo->prepare('INSERT INTO processos_movimentacoes
            (
                business_id,
                movimentacao_codigo,
                movimentacao_id_plataforma,
                movimentacao_data,
                movimentacao_tipo,
                movimentacao_tipo_publicacao,
                movimentacao_classificacao_predita_nome,
                movimentacao_classificacao_predita_descricao,
                movimentacao_classificacao_predita_hierarquia,
                movimentacao_conteudo,
                movimentacao_texto_categoria,
                movimentacao_fonte_processo_fonte_id,
                movimentacao_fonte_fonte_id,
                movimentacao_fonte_nome,
                movimentacao_fonte_tipo,
                movimentacao_fonte_sigla,
                movimentacao_fonte_grau,
                movimentacao_fonte_grau_formatado,
                processo_codigo
            )
            VALUES
            (
                :business_id,
                :movimentacao_codigo,
                :movimentacao_id_plataforma,
                :movimentacao_data,
                :movimentacao_tipo,
                :movimentacao_tipo_publicacao,
                :movimentacao_classificacao_predita_nome,
                :movimentacao_classificacao_predita_descricao,
                :movimentacao_classificacao_predita_hierarquia,
                :movimentacao_conteudo,
                :movimentacao_texto_categoria,
                :movimentacao_fonte_processo_fonte_id,
                :movimentacao_fonte_fonte_id,
                :movimentacao_fonte_nome,
                :movimentacao_fonte_tipo,
                :movimentacao_fonte_sigla,
                :movimentacao_fonte_grau,
                :movimentacao_fonte_grau_formatado,
                :processo_codigo
            )
        ');

        foreach($parametros->obterMovimentacoes() as $movimentacao){
            $pdo->execute([
                'business_id' => $parametros->empresaCodigo,
                'movimentacao_codigo' => $movimentacao->id,
                'movimentacao_id_plataforma' => $movimentacao->id,
                'movimentacao_data' => $movimentacao->data,
                'movimentacao_tipo' => $movimentacao->tipo,
                'movimentacao_tipo_publicacao' => $movimentacao->tipoPublicacao,
                'movimentacao_classificacao_predita_nome' => $movimentacao->classificacaoPreditaNome,
                'movimentacao_classificacao_predita_descricao' => $movimentacao->classificacaoPreditaDescricao,
                'movimentacao_classificacao_predita_hierarquia' => $movimentacao->classificacaoPreditaHierarquia,
                'movimentacao_conteudo' => $movimentacao->conteudo,
                'movimentacao_texto_categoria' => $movimentacao->textoCategoria,
                'movimentacao_fonte_processo_fonte_id' => $movimentacao->fonteProcessoFonteId,
                'movimentacao_fonte_fonte_id' => $movimentacao->fonteFonteId,
                'movimentacao_fonte_nome' => $movimentacao->fonteNome,
                'movimentacao_fonte_tipo' => $movimentacao->fonteTipo,
                'movimentacao_fonte_sigla' => $movimentacao->fonteSigla,
                'movimentacao_fonte_grau' => $movimentacao->fonteGrau,
                'movimentacao_fonte_grau_formatado' => $movimentacao->fonteGrauFormatado,
                'processo_codigo' => $parametros->CNJ
            ]);
        }
    }

    #[Override] public function obterProcessoPorCNJ(string $CNJ, string $empresaCodigo): ProcessoListagem
    {
        $pdo = $this->pdo->prepare(self::QUERY_PROCESSO_LISTAGEM. '
            WHERE business_id = :business_id
            AND processo_numero_cnj = :processo_numero_cnj
        ');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'processo_numero_cnj' => $CNJ
        ]);
        $processo = $pdo->fetch(PDO::FETCH_ASSOC);

        $processo_ultima_movimentacao_data = '';
        if(isset($processo['processo_ultima_movimentacao_data']) and !empty($processo['processo_ultima_movimentacao_data']) and $processo['processo_ultima_movimentacao_data'] != ' '){
            $processo_ultima_movimentacao_data = date('d/m/Y', strtotime($processo['processo_ultima_movimentacao_data']));
        }

        return new ProcessoListagem(
            codigo: (string) $processo['processo_codigo'],
            numeroCNJ: (string) $processo['processo_numero_cnj'],
            dataUltimaMovimentacao: date('d/m/Y', strtotime((string) $processo['processo_data_ultima_movimentacao'] ?? '')),
            quantidadeMovimentacoes: (int) $processo['processo_quantidade_movimentacoes'],
            demandante: (string) $processo['processo_demandante'] ?? '',
            demandado: (string) $processo['processo_demandado'] ?? '',
            ultimaMovimentacaoData: $processo_ultima_movimentacao_data,
            ultimaMovimentacaoDescricao: $processo['processo_ultima_movimentacao_descricao'] ?? '',
        );
    }
}
