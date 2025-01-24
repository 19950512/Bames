<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Cobranca;

use App\Dominio\Repositorios\Cobranca\Fronteiras\Boleto;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\EntradaFronteiraCriarUmaCobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\SaidaFronteiraCobrancasDoCliente;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use Exception;
use Override;
use PDO;

final class ImplementacaoRepositorioCobranca implements RepositorioCobranca
{

    /*
    CREATE TABLE IF NOT EXISTS cobrancas
    (
        codigo serial NOT NULL,
        business_id character varying NOT NULL,
        cobranca_id character varying COLLATE pg_catalog."default" NOT NULL,
        pagador_id character varying COLLATE pg_catalog."default" NOT NULL,
        conta_bancaria_id character varying COLLATE pg_catalog."default" NOT NULL,
        valor DECIMAL,
        data_vencimento DATE,
        mensagem TEXT,
        multa DECIMAL,
        juros DECIMAL,
        valor_desconto_antecipacao DECIMAL,
        tipo_desconto character varying COLLATE pg_catalog."default",
        tipo_juros character varying COLLATE pg_catalog."default",
        tipo_multa character varying COLLATE pg_catalog."default",
        autodata timestamp with time zone NOT NULL DEFAULT now(),
        CONSTRAINT cobrancas_pkey PRIMARY KEY (codigo)
    );


    CREATE TABLE IF NOT EXISTS cobrancas_eventos
    (
        codigo serial NOT NULL,
        business_id character varying NOT NULL,
        cobranca_id character varying COLLATE pg_catalog."default" NOT NULL,
        evento_momento character varying COLLATE pg_catalog."default" NOT NULL,
        evento_descricao character varying COLLATE pg_catalog."default" NOT NULL,
        CONSTRAINT cobrancas_eventos_pkey PRIMARY KEY (codigo)
    );
    */

    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function criarUmaCobranca(EntradaFronteiraCriarUmaCobranca $parametros): void
    {
        $sql = 'INSERT INTO cobrancas (
                       business_id,
                       cobranca_id,
                       pagador_id,
                       pagador_nome_completo,
                       conta_bancaria_id,
                       data_vencimento,
                       mensagem,
                       meio_de_pagamento,
                       multa,
                       juros,
                       parcelas,
                       valor_desconto_antecipacao,
                       tipo_desconto,
                       tipo_juros,
                       tipo_multa,
                       autodata
                ) VALUES (
                      :business_id,
                      :cobranca_id,
                      :pagador_id,
                      :pagador_nome_completo,
                      :conta_bancaria_id,
                      :data_vencimento,
                      :mensagem,
                      :meio_de_pagamento,
                      :multa,
                      :juros,
                      :parcelas,
                      :valor_desconto_antecipacao,
                      :tipo_desconto,
                      :tipo_juros,
                      :tipo_multa,
                      :agora
                )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'pagador_nome_completo' => $parametros->clienteNomeCompleto,
            'business_id' => $parametros->empresaCodigo,
            'cobranca_id' => $parametros->cobrancaCodigo,
            'pagador_id' => $parametros->clienteCodigo,
            'conta_bancaria_id' => $parametros->contaBancariaCodigo,
            'data_vencimento' => $parametros->dataVencimento,
            'meio_de_pagamento' => $parametros->meioDePagamento,
            'mensagem' => $parametros->mensagem,
            'multa' => $parametros->multa,
            'juros' => $parametros->juros,
            'parcelas' => $parametros->parcela,
            'valor_desconto_antecipacao' => $parametros->valorDescontoAntecipacao,
            'tipo_desconto' => $parametros->tipoDesconto,
            'tipo_juros' => $parametros->tipoJuros,
            'tipo_multa' => $parametros->tipoMulta,
            'agora' => date('Y-m-d H:i:s')
        ]);

        if(count($parametros->composicaoDaCobranca) > 0){

            $sql = 'INSERT INTO cobrancas_composicao (
                       business_id,
                       plano_de_contas_id,
                       plano_de_conta_nome,
                       cobranca_id,
                       descricao,
                       valor,
                       autodata
                ) VALUES (
                      :business_id,
                      :plano_de_contas_id,
                      :plano_de_conta_nome,
                      :cobranca_id,
                      :descricao,
                      :valor,
                      :agora
                )';

            $stmt = $this->pdo->prepare($sql);
            foreach ($parametros->composicaoDaCobranca as $composicao) {

                $stmt->execute([
                    'business_id' => $parametros->empresaCodigo,
                    'plano_de_contas_id' => $composicao['planoDeContasCodigo'],
                    'cobranca_id' => $parametros->cobrancaCodigo,
                    'plano_de_conta_nome' => $composicao['planoDeContaNome'],
                    'descricao' => $composicao['descricao'],
                    'valor' => $composicao['valor'],
                    'agora' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    #[Override] public function buscarTodasAsCobrancas(string $empresaCodigo): SaidaFronteiraCobrancasDoCliente
    {

        $sql = 'SELECT
                    cobranca_id,
                    business_id,
                    conta_bancaria_id,
                    cobranca_id_plataforma_api_cobranca,
                    pagador_id,
                    pagador_nome_completo,
                    data_vencimento,
                    mensagem,
                    multa,
                    juros,
                    parcelas,
                    valor_desconto_antecipacao,
                    tipo_desconto,
                    meio_de_pagamento,
                    tipo_juros,
                    tipo_multa
            FROM cobrancas 
            WHERE business_id = :business_id
            ORDER BY data_vencimento DESC
            ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo
        ]);
        $cobrancas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $composicaoDaCobranca = [];
            $sql = 'SELECT
                        plano_de_contas_id,
                        plano_de_conta_nome,
                        descricao,
                        valor
                FROM cobrancas_composicao 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

            $stmtComposicao = $this->pdo->prepare($sql);
            $stmtComposicao->execute([
                'cobranca_id' => $row['cobranca_id'],
                'business_id' => $row['business_id']
            ]);

            while ($rowComposicao = $stmtComposicao->fetch(PDO::FETCH_ASSOC)) {
                $composicaoDaCobranca[] = [
                    'planoDeContaCodigo' => (int) $rowComposicao['plano_de_contas_id'],
                    'planoDeContaNome' => (string) $rowComposicao['plano_de_conta_nome'],
                    'descricao' => $rowComposicao['descricao'],
                    'valor' => (float) $rowComposicao['valor']
                ];
            }

           $cobrancasTemp = new Cobranca(
               cobrancaCodigo: (string) $row['cobranca_id'],
               empresaCodigo: (string) $row['business_id'],
               contaBancariaCodigo: (string) $row['conta_bancaria_id'],
               clienteCodigo: (string) $row['pagador_id'],
               clienteNomeCompleto: (string) $row['pagador_nome_completo'] ?? '',
               dataVencimento: $row['data_vencimento'],
               mensagem: $row['mensagem'],
               meioDePagamento: (string) $row['meio_de_pagamento'],
               multa: (float) $row['multa'],
               composicaoDaCobranca: $composicaoDaCobranca,
               juros: (float) $row['juros'],
               valorDescontoAntecipacao: (float) $row['valor_desconto_antecipacao'],
               tipoDesconto: $row['tipo_desconto'],
               tipoJuros: $row['tipo_juros'],
               tipoMulta: $row['tipo_multa'],
               parcela: (int) $row['parcelas'],
               codigoNaPlataformaCobrancaAPI: (string) $row['cobranca_id_plataforma_api_cobranca'],
            );

           // Vamos ver se há os eventos para essa cobrança.
            $sql = 'SELECT
                        evento_momento,
                        evento_descricao
                FROM cobrancas_eventos 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id
                ORDER BY evento_momento DESC';


            $stmtEventos = $this->pdo->prepare($sql);
            $stmtEventos->execute([
                'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
                'business_id' => $cobrancasTemp->empresaCodigo
            ]);

            while ($rowEventos = $stmtEventos->fetch(PDO::FETCH_ASSOC)) {
                $cobrancasTemp->adicionarEvento($rowEventos['evento_momento'], $rowEventos['evento_descricao']);
            }

            // Vamos ver se há boletos para essa cobrança
            $sql = 'SELECT
                        boleto_id,
                        boleto_id_plataforma_api_cobranca,
                        boleto_nosso_numero,
                        boleto_url,
                        cobranca_id,
                        boleto_data_vencimento,
                        boleto_nosso_numero,
                        cliente_id,
                        boleto_mensagem,
                        boleto_valor,
                        boleto_status,
                        boleto_linha_digitavel,
                        boleto_codigo_barras
                FROM boletos 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

            $stmtBoletos = $this->pdo->prepare($sql);

            $stmtBoletos->execute([
                'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
                'business_id' => $cobrancasTemp->empresaCodigo
            ]);

            while ($rowBoletos = $stmtBoletos->fetch(PDO::FETCH_ASSOC)) {
                $boletoTemp = new Boleto(
                    boletoCodigo: $rowBoletos['boleto_id'],
                    boletoCodigoNaPlataformaCobrancaAPI: (string) ($rowBoletos['boleto_id_plataforma_api_cobranca'] ?? ''),
                    cobrancaCodigo: (string) $rowBoletos['cobranca_id'],
                    pagadorCodigo: (string) ($rowBoletos['cliente_id'] ?? ''),
                    status: (string) ($rowBoletos['boleto_status'] ?? ''),
                    vencimento: (string) ($rowBoletos['boleto_data_vencimento'] ?? ''),
                    nossoNumero: (string) ($rowBoletos['boleto_nosso_numero'] ?? ''),
                    codigoDeBarras: (string) ($rowBoletos['boleto_codigo_barras'] ?? ''),
                    linhaDigitavel: (string) ($rowBoletos['boleto_linha_digitavel'] ?? ''),
                    linkBoleto: (string) ($rowBoletos['boleto_url'] ?? ''),
                    mensagem: (string) ($rowBoletos['boleto_mensagem'] ?? ''),
                    valor: (float) $rowBoletos['boleto_valor']
                );
                $cobrancasTemp->adicionarBoleto($boletoTemp);
            }

             $cobrancas[] = $cobrancasTemp;
        }
        $saida = new SaidaFronteiraCobrancasDoCliente();
        foreach ($cobrancas as $cobranca) {
            $saida->adicionarCobranca($cobranca);
        }

        return $saida;
    }

    #[Override] public function buscarCobrancasDoCliente(string $clienteCodigo, string $empresaCodigo): SaidaFronteiraCobrancasDoCliente
    {
        $sql = 'SELECT
                    cobranca_id,
                    business_id,
                    conta_bancaria_id,
                    pagador_id,
                    pagador_nome_completo,
                    data_vencimento,
                    mensagem,
                    multa,
                    juros,
                    cobranca_id_plataforma_api_cobranca,
                    parcelas,
                    valor_desconto_antecipacao,
                    tipo_desconto,
                    meio_de_pagamento,
                    tipo_juros,
                    tipo_multa
            FROM cobrancas 
            WHERE pagador_id = :pagador_id AND business_id = :business_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'pagador_id' => $clienteCodigo,
            'business_id' => $empresaCodigo
        ]);
        $cobrancas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $composicaoDaCobranca = [];
            $sql = 'SELECT
                        plano_de_contas_id,
                        plano_de_conta_nome,
                        descricao,
                        valor
                FROM cobrancas_composicao 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

            $stmtComposicao = $this->pdo->prepare($sql);
            $stmtComposicao->execute([
                'cobranca_id' => $row['cobranca_id'],
                'business_id' => $row['business_id']
            ]);

            while ($rowComposicao = $stmtComposicao->fetch(PDO::FETCH_ASSOC)) {
                $composicaoDaCobranca[] = [
                    'planoDeContaCodigo' => (int) $rowComposicao['plano_de_contas_id'],
                    'descricao' => $rowComposicao['descricao'],
                    'planoDeContaNome' => (string) $rowComposicao['plano_de_conta_nome'],
                    'valor' => (float) $rowComposicao['valor']
                ];
            }
            $cobrancasTemp = new Cobranca(
                cobrancaCodigo: (string) $row['cobranca_id'],
                empresaCodigo: (string) $row['business_id'],
                contaBancariaCodigo: (string) $row['conta_bancaria_id'],
                clienteCodigo: (string) $row['pagador_id'],
               clienteNomeCompleto: (string) $row['pagador_nome_completo'] ?? '',
                dataVencimento: $row['data_vencimento'],
                mensagem: $row['mensagem'],
                meioDePagamento: (string) $row['meio_de_pagamento'],
                multa: (float) $row['multa'],
                composicaoDaCobranca: $composicaoDaCobranca,
                juros: (float) $row['juros'],
                valorDescontoAntecipacao: (float) $row['valor_desconto_antecipacao'],
                tipoDesconto: $row['tipo_desconto'],
                tipoJuros: $row['tipo_juros'],
                tipoMulta: $row['tipo_multa'],
                parcela: (int) $row['parcelas'],
                codigoNaPlataformaCobrancaAPI: (string) $row['cobranca_id_plataforma_api_cobranca'],
            );

           // Vamos ver se há os eventos para essa cobrança.
            $sql = 'SELECT
                        evento_momento,
                        evento_descricao
                FROM cobrancas_eventos 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

            $stmtEventos = $this->pdo->prepare($sql);
            $stmtEventos->execute([
                'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
                'business_id' => $cobrancasTemp->empresaCodigo
            ]);

            while ($rowEventos = $stmtEventos->fetch(PDO::FETCH_ASSOC)) {
                $cobrancasTemp->adicionarEvento($rowEventos['evento_momento'], $rowEventos['evento_descricao']);
            }

            // Vamos ver se há boletos para essa cobrança
            $sql = 'SELECT
                        boleto_id,
                        boleto_id_plataforma_api_cobranca,
                        boleto_nosso_numero,
                        boleto_url,
                        boleto_data_vencimento,
                        cliente_id,
                        cobranca_id,
                        boleto_valor,
                        boleto_status,
                        boleto_linha_digitavel,
                        boleto_mensagem,
                        boleto_codigo_barras
                FROM boletos 
                WHERE cobranca_id = :cobranca_id AND business_id = :business_id';


            $stmtBoletos = $this->pdo->prepare($sql);

            $stmtBoletos->execute([
                'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
                'business_id' => $cobrancasTemp->empresaCodigo
            ]);

            while ($rowBoletos = $stmtBoletos->fetch(PDO::FETCH_ASSOC)) {
                $boletoTemp = new Boleto(
                    boletoCodigo: $rowBoletos['boleto_id'],
                    boletoCodigoNaPlataformaCobrancaAPI: (string) ($rowBoletos['boleto_id_plataforma_api_cobranca'] ?? ''),
                    cobrancaCodigo: (string) $rowBoletos['cobranca_id'],
                    pagadorCodigo: (string) ($rowBoletos['cliente_id'] ?? ''),
                    status: (string) ($rowBoletos['boleto_status'] ?? ''),
                    vencimento: (string) ($rowBoletos['boleto_data_vencimento'] ?? ''),
                    nossoNumero: (string) ($rowBoletos['boleto_nosso_numero'] ?? ''),
                    codigoDeBarras: (string) ($rowBoletos['boleto_codigo_barras'] ?? ''),
                    linhaDigitavel: (string) ($rowBoletos['boleto_linha_digitavel'] ?? ''),
                    linkBoleto: (string) ($rowBoletos['boleto_url'] ?? ''),
                    mensagem: (string) ($rowBoletos['boleto_mensagem'] ?? ''),
                    valor: (float) $rowBoletos['boleto_valor']
                );
                $cobrancasTemp->adicionarBoleto($boletoTemp);
            }

             $cobrancas[] = $cobrancasTemp;
        }
        $saida = new SaidaFronteiraCobrancasDoCliente();
        foreach ($cobrancas as $cobranca) {
            $saida->adicionarCobranca($cobranca);
        }
        return $saida;
    }

    #[Override] public function buscarCobrancaPorCodigoDaPlataformaAPI(string $cobrancaCodigoPlataforma, string $empresaCodigo): Cobranca
    {

        $sql = 'SELECT
                    cobranca_id,
                    business_id,
                    conta_bancaria_id,
                    pagador_id,
                    pagador_nome_completo,
                    data_vencimento,
                    mensagem,
                    multa,
                    juros,
                    parcelas,
                    meio_de_pagamento,
                    valor_desconto_antecipacao,
                    tipo_desconto,
                    cobranca_id_plataforma_api_cobranca,
                    tipo_juros,
                    tipo_multa
            FROM cobrancas 
            WHERE cobranca_id_plataforma_API_cobranca = :cobrancaCodigoPlataforma AND business_id = :business_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cobrancaCodigoPlataforma' => $cobrancaCodigoPlataforma,
            'business_id' => $empresaCodigo
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!isset($row['cobranca_id'])){
            throw new Exception('Cobrança não encontrada para o código da plataforma informado. '.$cobrancaCodigoPlataforma);
        }

        $composicaoDaCobranca = [];
        $sql = 'SELECT
                    plano_de_contas_id,
                    plano_de_conta_nome,
                    descricao,
                    valor
            FROM cobrancas_composicao 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

        $stmtComposicao = $this->pdo->prepare($sql);
        $stmtComposicao->execute([
            'cobranca_id' => $row['cobranca_id'],
            'business_id' => $row['business_id']
        ]);

        while ($rowComposicao = $stmtComposicao->fetch(PDO::FETCH_ASSOC)) {
            $composicaoDaCobranca[] = [
                'planoDeContaCodigo' => (int) $rowComposicao['plano_de_contas_id'],
                'descricao' => $rowComposicao['descricao'],
                'planoDeContaNome' => (string) $rowComposicao['plano_de_conta_nome'],
                'valor' => (float) $rowComposicao['valor']
            ];
        }

        $cobrancasTemp = new Cobranca(
            cobrancaCodigo: (string) $row['cobranca_id'],
            empresaCodigo: (string) $row['business_id'],
            contaBancariaCodigo: (string) $row['conta_bancaria_id'],
            clienteCodigo: (string) $row['pagador_id'],
            clienteNomeCompleto: (string) $row['pagador_nome_completo'] ?? '',
            dataVencimento: $row['data_vencimento'],
            mensagem: $row['mensagem'],
            meioDePagamento: (string) $row['meio_de_pagamento'],
            multa: (float) $row['multa'],
            composicaoDaCobranca: $composicaoDaCobranca,
            juros: (float) $row['juros'],
            valorDescontoAntecipacao: (float) $row['valor_desconto_antecipacao'],
            tipoDesconto: $row['tipo_desconto'],
            tipoJuros: $row['tipo_juros'],
            tipoMulta: $row['tipo_multa'],
            parcela: (int) $row['parcelas'],
            codigoNaPlataformaCobrancaAPI: (string) $row['cobranca_id_plataforma_api_cobranca'],
        );

       // Vamos ver se há os eventos para essa cobrança.
        $sql = 'SELECT
                    evento_momento,
                    evento_descricao
            FROM cobrancas_eventos 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id
            ORDER BY evento_momento DESC';

        $stmtEventos = $this->pdo->prepare($sql);
        $stmtEventos->execute([
            'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
            'business_id' => $cobrancasTemp->empresaCodigo
        ]);

        while ($rowEventos = $stmtEventos->fetch(PDO::FETCH_ASSOC)) {
            $cobrancasTemp->adicionarEvento($rowEventos['evento_momento'], $rowEventos['evento_descricao']);
        }

        // Vamos ver se há boletos para essa cobrança
        $sql = 'SELECT
                    boleto_id,
                    boleto_id_plataforma_api_cobranca,
                    boleto_nosso_numero,
                    boleto_url,
                    boleto_data_vencimento,
                    boleto_valor,
                    cobranca_id,
                    cliente_id,
                    boleto_status,
                        boleto_mensagem,
                    boleto_linha_digitavel,
                    boleto_codigo_barras
            FROM boletos 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

        $stmtBoletos = $this->pdo->prepare($sql);

        $stmtBoletos->execute([
            'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
            'business_id' => $cobrancasTemp->empresaCodigo
        ]);

        while ($rowBoletos = $stmtBoletos->fetch(PDO::FETCH_ASSOC)) {
            $boletoTemp = new Boleto(
                boletoCodigo: $rowBoletos['boleto_id'],
                boletoCodigoNaPlataformaCobrancaAPI: (string) ($rowBoletos['boleto_id_plataforma_api_cobranca'] ?? ''),
                cobrancaCodigo: (string) $rowBoletos['cobranca_id'],
                pagadorCodigo: (string) ($rowBoletos['cliente_id'] ?? ''),
                status: (string) ($rowBoletos['boleto_status'] ?? ''),
                vencimento: (string) ($rowBoletos['boleto_data_vencimento'] ?? ''),
                nossoNumero: (string) ($rowBoletos['boleto_nosso_numero'] ?? ''),
                codigoDeBarras: (string) ($rowBoletos['boleto_codigo_barras'] ?? ''),
                linhaDigitavel: (string) ($rowBoletos['boleto_linha_digitavel'] ?? ''),
                linkBoleto: (string) ($rowBoletos['boleto_url'] ?? ''),
                mensagem: (string) ($rowBoletos['boleto_mensagem'] ?? ''),
                valor: (float) $rowBoletos['boleto_valor']
            );
            $cobrancasTemp->adicionarBoleto($boletoTemp);
        }

        return $cobrancasTemp;
    }

    #[Override] public function buscarCobrancaPorCodigo(string $cobrancaCodigo, string $empresaCodigo): Cobranca
    {

        $sql = 'SELECT
                    cobranca_id,
                    business_id,
                    conta_bancaria_id,
                    pagador_id,
                    pagador_nome_completo,
                    data_vencimento,
                    mensagem,
                    multa,
                    juros,
                    parcelas,
                    meio_de_pagamento,
                    valor_desconto_antecipacao,
                    tipo_desconto,
                    cobranca_id_plataforma_api_cobranca,
                    tipo_juros,
                    tipo_multa
            FROM cobrancas 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cobranca_id' => $cobrancaCodigo,
            'business_id' => $empresaCodigo
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!isset($row['cobranca_id'])){
            throw new Exception('Cobrança não encontrada para o código informado. '.$cobrancaCodigo);
        }

        $composicaoDaCobranca = [];
        $sql = 'SELECT
                    plano_de_contas_id,
                    plano_de_conta_nome,
                    descricao,
                    valor
            FROM cobrancas_composicao 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

        $stmtComposicao = $this->pdo->prepare($sql);
        $stmtComposicao->execute([
            'cobranca_id' => $row['cobranca_id'],
            'business_id' => $row['business_id']
        ]);

        while ($rowComposicao = $stmtComposicao->fetch(PDO::FETCH_ASSOC)) {
            $composicaoDaCobranca[] = [
                'planoDeContaCodigo' => (int) $rowComposicao['plano_de_contas_id'],
                'descricao' => $rowComposicao['descricao'],
                'planoDeContaNome' => (string) $rowComposicao['plano_de_conta_nome'],
                'valor' => (float) $rowComposicao['valor']
            ];
        }

        $cobrancasTemp = new Cobranca(
            cobrancaCodigo: (string) $row['cobranca_id'],
            empresaCodigo: (string) $row['business_id'],
            contaBancariaCodigo: (string) $row['conta_bancaria_id'],
            clienteCodigo: (string) $row['pagador_id'],
               clienteNomeCompleto: (string) $row['pagador_nome_completo'] ?? '',
            dataVencimento: $row['data_vencimento'],
            mensagem: $row['mensagem'],
            meioDePagamento: (string) $row['meio_de_pagamento'],
            multa: (float) $row['multa'],
            composicaoDaCobranca: $composicaoDaCobranca,
            juros: (float) $row['juros'],
            valorDescontoAntecipacao: (float) $row['valor_desconto_antecipacao'],
            tipoDesconto: $row['tipo_desconto'],
            tipoJuros: $row['tipo_juros'],
            tipoMulta: $row['tipo_multa'],
            parcela: (int) $row['parcelas'],
            codigoNaPlataformaCobrancaAPI: (string) $row['cobranca_id_plataforma_api_cobranca'],
        );

       // Vamos ver se há os eventos para essa cobrança.
        $sql = 'SELECT
                    evento_momento,
                    evento_descricao
            FROM cobrancas_eventos 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id
            ORDER BY evento_momento DESC';

        $stmtEventos = $this->pdo->prepare($sql);
        $stmtEventos->execute([
            'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
            'business_id' => $cobrancasTemp->empresaCodigo
        ]);

        while ($rowEventos = $stmtEventos->fetch(PDO::FETCH_ASSOC)) {
            $cobrancasTemp->adicionarEvento($rowEventos['evento_momento'], $rowEventos['evento_descricao']);
        }

        // Vamos ver se há boletos para essa cobrança
        $sql = 'SELECT
                    boleto_id,
                    boleto_id_plataforma_api_cobranca,
                    boleto_nosso_numero,
                    boleto_url,
                    cobranca_id,
                    boleto_data_vencimento,
                    boleto_valor,
                    cliente_id,
                        boleto_mensagem,
                    boleto_status,
                    boleto_linha_digitavel,
                    boleto_codigo_barras
            FROM boletos 
            WHERE cobranca_id = :cobranca_id AND business_id = :business_id';

        $stmtBoletos = $this->pdo->prepare($sql);

        $stmtBoletos->execute([
            'cobranca_id' => $cobrancasTemp->cobrancaCodigo,
            'business_id' => $cobrancasTemp->empresaCodigo
        ]);

        while ($rowBoletos = $stmtBoletos->fetch(PDO::FETCH_ASSOC)) {
            $boletoTemp = new Boleto(
                boletoCodigo: $rowBoletos['boleto_id'],
                boletoCodigoNaPlataformaCobrancaAPI: (string) ($rowBoletos['boleto_id_plataforma_api_cobranca'] ?? ''),
                cobrancaCodigo: (string) $rowBoletos['cobranca_id'],
                pagadorCodigo: (string) ($rowBoletos['cliente_id'] ?? ''),
                status: (string) ($rowBoletos['boleto_status'] ?? ''),
                vencimento: (string) ($rowBoletos['boleto_data_vencimento'] ?? ''),
                nossoNumero: (string) ($rowBoletos['boleto_nosso_numero'] ?? ''),
                codigoDeBarras: (string) ($rowBoletos['boleto_codigo_barras'] ?? ''),
                linhaDigitavel: (string) ($rowBoletos['boleto_linha_digitavel'] ?? ''),
                linkBoleto: (string) ($rowBoletos['boleto_url'] ?? ''),
                mensagem: (string) ($rowBoletos['boleto_mensagem'] ?? ''),
                valor: (float) $rowBoletos['boleto_valor']
            );
            $cobrancasTemp->adicionarBoleto($boletoTemp);
        }

        return $cobrancasTemp;
    }

    public function atualizarCodigoDaCobrancaNaPlataforma(string $cobrancaCodigo, string $empresaCodigo, string $codigoDaCobrancaNaPlataformaDeCobranca): void
    {
        $sql = 'UPDATE cobrancas SET cobranca_id_plataforma_API_cobranca = :cobranca_id_plataforma_API_cobranca WHERE cobranca_id = :cobranca_id AND business_id = :business_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cobranca_id_plataforma_API_cobranca' => $codigoDaCobrancaNaPlataformaDeCobranca,
            'cobranca_id' => $cobrancaCodigo,
            'business_id' => $empresaCodigo
        ]);
    }

    #[Override] public function novoEvento(string $cobrancaCodigo, string $empresaCodigo, string $descricao): void
    {
        $sql = 'INSERT INTO cobrancas_eventos (
                       business_id,
                       cobranca_id,
                       evento_momento,
                       evento_descricao
                ) VALUES (
                      :business_id,
                      :cobranca_id,
                      :evento_momento,
                      :evento_descricao
                )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'cobranca_id' => $cobrancaCodigo,
            'evento_momento' => date('Y-m-d H:i:s'),
            'evento_descricao' => $descricao
        ]);
    }
}
