<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Boleto;

use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraBoletoFoiPagoNaPlataformaCobranca;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraCriarBoleto;
use App\Dominio\Repositorios\Boleto\Fronteiras\SaidaFronteiraBoleto;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use Exception;
use Override;
use PDO;

class ImplementacaoRepositorioBoleto implements RepositorioBoleto
{

    public function __construct(
        private PDO $pdo,
    ){}

    /*
    CREATE TABLE IF NOT EXISTS boletos
    (
        codigo serial NOT NULL,
        business_id character varying NOT NULL,
        boleto_id character varying COLLATE pg_catalog."default" NOT NULL,
        boleto_id_plataforma_API_cobranca character varying COLLATE pg_catalog."default" NOT NULL,
        cobranca_id character varying COLLATE pg_catalog."default" NOT NULL,
        cliente_id character varying COLLATE pg_catalog."default",
        conta_bancaria_id character varying COLLATE pg_catalog."default",
        boleto_pagador_id_plataforma_API_cobranca character varying COLLATE pg_catalog."default" NOT NULL,
        boleto_valor DECIMAL,
        boleto_data_vencimento DATE,
        boleto_mensagem TEXT,
        boleto_multa DECIMAL,
        boleto_juros DECIMAL,
        boleto_seu_numero character varying COLLATE pg_catalog."default",
        boleto_nosso_numero character varying COLLATE pg_catalog."default",
        boleto_linha_digitavel character varying COLLATE pg_catalog."default",
        boleto_codigo_barras character varying COLLATE pg_catalog."default",
        boleto_url character varying COLLATE pg_catalog."default",
        boleto_status character varying COLLATE pg_catalog."default",
        CONSTRAINT boletos_pkey PRIMARY KEY (codigo)
    );
    */

    #[Override] public function boletoFoiAceitoPelaPlataforma(string $empresaCodigo, string $novoStatus, string $boletoCodigo): void
    {

        $sql = "UPDATE boletos SET
            boleto_status = :boleto_status,
            boleto_foi_aceito_pela_plataforma = true
        WHERE business_id = :business_id AND boleto_id = :boleto_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'boleto_id' => $boletoCodigo,
            'boleto_status' => $novoStatus,
        ]);
    }

    #[Override] public function buscarBoletoPorCodigoNaPlataforma(string $codigoBoletoNaPlataformaAPI, string $empresaCodigo): SaidaFronteiraBoleto
    {

        $sql = "SELECT
                boleto_id,
                business_id,
                boleto_valor,
                boleto_id_plataforma_api_cobranca,
                conta_bancaria_id,
                boleto_data_vencimento,
                cobranca_id,
                boleto_foi_aceito_pela_plataforma,
                boleto_status,
                boleto_seu_numero,
                boleto_mensagem,
                cliente_id,
                boleto_nosso_numero,
                boleto_linha_digitavel,
                boleto_codigo_barras,
                boleto_qrcode_pix,
                boleto_url
            FROM boletos
            WHERE boleto_id_plataforma_api_cobranca = :boleto_id_plataforma_api_cobranca AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'boleto_id_plataforma_api_cobranca' => $codigoBoletoNaPlataformaAPI,
            'business_id' => $empresaCodigo,
        ]);

        $boleto = $stmt->fetch();

        if(!isset($boleto['boleto_id']) or empty($boleto['boleto_id'])){
            throw new Exception("Boleto não encontrado.");
        }

        return new SaidaFronteiraBoleto(
            codigoBoleto: (string) $boleto['boleto_id'],
            empresaCodigo: (string) $boleto['business_id'],
            cobrancaCodigo: (string) $boleto['cobranca_id'],
            codigoBoletoNaPlataformaAPICobranca: (string) $boleto['boleto_id_plataforma_api_cobranca'],
            contaBancariaCodigo: (string) $boleto['conta_bancaria_id'],
            valor: (float) $boleto['boleto_valor'],
            dataVencimento: (string) $boleto['boleto_data_vencimento'],
            statusBoleto: (string) $boleto['boleto_status'],
            linkBoleto: (string) $boleto['boleto_url'],
            nossoNumero: (string) $boleto['boleto_nosso_numero'],
            seuNumero: (string) $boleto['boleto_seu_numero'],
            codigoDeBarras: (string) $boleto['boleto_codigo_barras'],
            linhaDigitavel: (string) $boleto['boleto_linha_digitavel'],
            mensagem: (string) $boleto['boleto_mensagem'],
            pagadorCodigo: (string) $boleto['cliente_id'],
            qrCode: (string) $boleto['boleto_qrcode_pix'],
            foiAceitoPelaPlataforma: (bool) $boleto['boleto_foi_aceito_pela_plataforma']
        );
    }

    public function buscarBoletoPorCodigo(string $codigoBoleto, string $empresaCodigo): SaidaFronteiraBoleto
    {

        $sql = "SELECT
                boleto_id,
                business_id,
                boleto_valor,
                boleto_id_plataforma_api_cobranca,
                conta_bancaria_id,
                boleto_data_vencimento,
                cobranca_id,
                boleto_mensagem,
                cliente_id,
                boleto_foi_aceito_pela_plataforma,
                boleto_status,
                boleto_seu_numero,
                boleto_nosso_numero,
                boleto_linha_digitavel,
                boleto_codigo_barras,
                boleto_qrcode_pix,
                boleto_url
            FROM boletos
            WHERE boleto_id = :boleto_id AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'boleto_id' => $codigoBoleto,
            'business_id' => $empresaCodigo,
        ]);

        $boleto = $stmt->fetch();

        if(!isset($boleto['boleto_id']) or empty($boleto['boleto_id'])){
            throw new Exception("Boleto não encontrado.");
        }

        return new SaidaFronteiraBoleto(
            codigoBoleto: (string) $boleto['boleto_id'],
            empresaCodigo: (string) $boleto['business_id'],
            cobrancaCodigo: (string) $boleto['cobranca_id'],
            codigoBoletoNaPlataformaAPICobranca: (string) $boleto['boleto_id_plataforma_api_cobranca'],
            contaBancariaCodigo: (string) $boleto['conta_bancaria_id'],
            valor: (float) $boleto['boleto_valor'],
            dataVencimento: (string) $boleto['boleto_data_vencimento'],
            statusBoleto: (string) $boleto['boleto_status'],
            linkBoleto: (string) $boleto['boleto_url'],
            nossoNumero: (string) $boleto['boleto_nosso_numero'],
            seuNumero: (string) $boleto['boleto_seu_numero'],
            codigoDeBarras: (string) $boleto['boleto_codigo_barras'],
            linhaDigitavel: (string) $boleto['boleto_linha_digitavel'],
            mensagem: (string) $boleto['boleto_mensagem'],
            pagadorCodigo: (string) $boleto['cliente_id'],
            qrCode: (string) $boleto['boleto_qrcode_pix'],
            foiAceitoPelaPlataforma: (bool) $boleto['boleto_foi_aceito_pela_plataforma']
        );
    }

    #[Override] public function criarBoleto(EntradaFronteiraCriarBoleto $parametrosEntrada): void
    {
        $sql = "INSERT INTO boletos (
            business_id,
            boleto_id,
            boleto_id_plataforma_api_cobranca,
            cobranca_id_plataforma_api_cobranca,
            cobranca_id,
            cliente_id,
            conta_bancaria_id,
            boleto_valor,
            boleto_data_vencimento,
            boleto_mensagem,
            boleto_multa,
            boleto_juros,
            boleto_seu_numero,
            boleto_status
        ) VALUES (
            :business_id,
            :boleto_id,
            :boleto_id_plataforma_api_cobranca,
            :cobranca_id_plataforma_api_cobranca,
            :cobranca_id,
            :cliente_id,
            :conta_bancaria_id,
            :boleto_valor,
            :boleto_data_vencimento,
            :boleto_mensagem,
            :boleto_multa,
            :boleto_juros,
            :boleto_seu_numero,
            :boleto_status
        )";

        try {

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'boleto_id_plataforma_api_cobranca' => $parametrosEntrada->boletoCodigoNaPlataforma,
                'business_id' => $parametrosEntrada->empresaCodigo,
                'cobranca_id_plataforma_api_cobranca' => $parametrosEntrada->cobrancaCodigoNaPlataforma,
                'boleto_id' => $parametrosEntrada->boleto_id,
                'cobranca_id' => $parametrosEntrada->cobranca_id,
                'cliente_id' => $parametrosEntrada->cliente_id,
                'conta_bancaria_id' => $parametrosEntrada->conta_bancaria_id,
                'boleto_valor' => $parametrosEntrada->valor,
                'boleto_data_vencimento' => $parametrosEntrada->data_vencimento,
                'boleto_mensagem' => $parametrosEntrada->mensagem,
                'boleto_multa' => $parametrosEntrada->multa,
                'boleto_juros' => $parametrosEntrada->juros,
                'boleto_seu_numero' => $parametrosEntrada->seu_numero,
                'boleto_status' => $parametrosEntrada->status,
            ]);
        }catch (Exception $e){
            throw new Exception("Erro ao criar boleto: " . $e->getMessage());
        }
    }

    #[Override] public function boletofoiLiquidadoManualmente(string $empresaCodigo, string $boletoQuemLiquidouManualmente, string $novoStatus, string $dataPagamento, string $boletoCodigo, float $valorRecebido): void
    {

        $sql = "UPDATE boletos SET
            boleto_status = :boleto_status,
            boleto_foi_liquidado_manualmente = true,
            boleto_quem_liquidou_manualmente = :boleto_quem,
            boleto_quando_liquidou_manualmente = :boleto_quando,
            boleto_data_pagamento = :boleto_data_pagamento,
            boleto_valor_recebido = :boleto_valor_recebido
        WHERE business_id = :business_id AND boleto_id = :boleto_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'boleto_valor_recebido' => $valorRecebido,
            'boleto_data_pagamento' => $dataPagamento,
            'business_id' => $empresaCodigo,
            'boleto_id' => $boletoCodigo,
            'boleto_status' => $novoStatus,
            'boleto_quem' => $boletoQuemLiquidouManualmente,
            'boleto_quando' => date('Y-m-d H:i:s'),
        ]);
    }

    #[Override] public function boletoFoiPagoNaPlataforma(string $empresaCodigo, string $novoStatus, string $dataPagamento, string $boletoCodigo, float $valorRecebido): void
    {

        $sql = "UPDATE boletos SET
            boleto_status = :boleto_status,
            boleto_data_pagamento = :boleto_data_pagamento,
            boleto_valor_recebido = :boleto_valor_recebido
        WHERE business_id = :business_id AND boleto_id = :boleto_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'boleto_valor_recebido' => $valorRecebido,
            'boleto_data_pagamento' => $dataPagamento,
            'business_id' => $empresaCodigo,
            'boleto_id' => $boletoCodigo,
            'boleto_status' => $novoStatus,
        ]);
    }

    #[Override] public function existeUmBoletoNoSistemaComEsseCodigoDePlataformaDeCobranca(string $codigoBoletoNaPlataformaAPI, string $empresaCodigo): bool
    {

        $sql = "SELECT
            boleto_id
        FROM boletos
        WHERE boleto_id_plataforma_API_cobranca = :boleto_id_plataforma_api_cobranca AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'boleto_id_plataforma_api_cobranca' => $codigoBoletoNaPlataformaAPI,
            'business_id' => $empresaCodigo,
        ]);

        $boleto = $stmt->fetch();

        return isset($boleto['boleto_id']) and !empty($boleto['boleto_id']);
    }


    #[Override] public function boletoFoiEmitidoNaPlataformaCobranca(EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca $parametrosEntrada): void
    {

        $sql = "UPDATE boletos SET
            boleto_id_plataforma_API_cobranca = :boleto_id_plataforma_API_cobranca,
            cobranca_id_plataforma_API_cobranca = :cobranca_id_plataforma_API_cobranca,
            boleto_pagador_id_plataforma_API_cobranca = :boleto_pagador_id_plataforma_API_cobranca,
            boleto_nosso_numero = :nosso_numero,
            boleto_linha_digitavel = :linha_digitavel,
            boleto_codigo_barras = :codigo_barras,
            boleto_resposta_completa_plataforma = :resposta_completa,
            boleto_url = :url,
            boleto_status = :status
        WHERE business_id = :business_id AND boleto_id = :boleto_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $parametrosEntrada->business_id,
            'boleto_id' => $parametrosEntrada->boleto_id,
            'cobranca_id_plataforma_API_cobranca' => $parametrosEntrada->cobranca_id_plataforma_API_cobranca,
            'boleto_id_plataforma_API_cobranca' => $parametrosEntrada->boleto_id_plataforma_API_cobranca,
            'boleto_pagador_id_plataforma_API_cobranca' => $parametrosEntrada->boleto_pagador_id_plataforma_API_cobranca,
            'nosso_numero' => $parametrosEntrada->nosso_numero,
            'resposta_completa' => $parametrosEntrada->respostaCompletaDaPlataforma,
            'linha_digitavel' => $parametrosEntrada->linha_digitavel,
            'codigo_barras' => $parametrosEntrada->codigo_barras,
            'url' => $parametrosEntrada->url,
            'status' => $parametrosEntrada->status,
        ]);
    }

    #[Override] public function boletoFoiCancelado(string $empresaCodigo, string $boletoCodigo, string $boletoStatus): void
    {

        $sql = "UPDATE boletos SET
            boleto_status = :boleto_status
        WHERE business_id = :business_id AND boleto_id = :boleto_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'boleto_id' => $boletoCodigo,
            'boleto_status' => $boletoStatus,
        ]);
    }


    #[Override] public function atualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI(EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI $parametros): void
    {

        $sql = "UPDATE boletos SET
            boleto_pagador_id_plataforma_API_cobranca = :boleto_pagador_id_plataforma_API_cobranca,
            boleto_nosso_numero = :nosso_numero,
            boleto_linha_digitavel = :linha_digitavel,
            boleto_codigo_barras = :codigo_barras,
            boleto_mensagem = :mensagem,
            boleto_valor = :boleto_valor,
            cobranca_id_plataforma_api_cobranca = :cobranca_id_plataforma_api_cobranca,
            boleto_resposta_completa_plataforma = :resposta_completa,
            boleto_url = :url,
            boleto_foi_aceito_pela_plataforma = :boleto_foi_aceito_pela_plataforma,
            boleto_status = :status
        WHERE business_id = :business_id AND boleto_id_plataforma_API_cobranca = :boleto_id_plataforma_API_cobranca";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'cobranca_id_plataforma_api_cobranca' => $parametros->codigoCobrancaNaPlataformaAPI,
            'business_id' => $parametros->empresaCodigo,
            'boleto_foi_aceito_pela_plataforma' => !empty($parametros->linhaDigitavel),
            'mensagem' => $parametros->mensagem,
            'boleto_id_plataforma_API_cobranca' => $parametros->codigoBoletoNaPlataformaAPI,
            'boleto_pagador_id_plataforma_API_cobranca' => $parametros->codigoPagadorIDPlataformaAPI,
            'nosso_numero' => $parametros->nossoNumero,
            'boleto_valor' => $parametros->valor,
            'resposta_completa' => $parametros->respostaCompletaDaPlataforma,
            'linha_digitavel' => $parametros->linhaDigitavel,
            'codigo_barras' => $parametros->codigoDeBarras,
            'url' => $parametros->urlBoleto,
            'status' => $parametros->status,
        ]);
    }
}
