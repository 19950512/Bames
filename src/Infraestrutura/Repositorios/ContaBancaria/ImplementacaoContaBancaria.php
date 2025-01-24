<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\ContaBancaria;

use App\Dominio\Repositorios\ContaBancaria\Fronteiras\ContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\EntradaFronteiraAtualizarContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraTodasAsContasBancarias;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use Exception;
use Override;
use PDO;

final class ImplementacaoContaBancaria implements RepositorioContaBancaria
{
    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function buscarContaBancariaPorCodigo(string $contaBancariaCodigo, string $empresaCodigo): SaidaFronteiraContaBancaria
    {
        $sql = "SELECT 
            conta_bancaria_codigo,
            nome,
            client_id_api,
            chave_api,
            ambiente,
            banco
        FROM contas_bancarias
        WHERE business_id = :business_id
        AND conta_bancaria_codigo = :conta_bancaria_codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo
        ]);
        $contaBancaria = $stmt->fetch();

        if(!isset($contaBancaria['conta_bancaria_codigo'])){
            throw new Exception("Nenhuma conta bancária encontrada com esse código, ".$contaBancariaCodigo);
        }

        return new SaidaFronteiraContaBancaria(
            codigo: $contaBancaria['conta_bancaria_codigo'],
            nome: $contaBancaria['nome'],
            banco: $contaBancaria['banco'],
            ambiente: $contaBancaria['ambiente'] ?? 'Sandbox',
            chaveAPI: (string) $contaBancaria['chave_api'] ?? '',
            clientIDAPI: (string) ($contaBancaria['client_id_api'] ?? '')
        );
    }
    #[Override] public function buscarAPrimeiraContaBancaria(string $empresaCodigo): SaidaFronteiraContaBancaria
    {
        $sql = "SELECT 
            conta_bancaria_codigo,
            nome,
            ambiente,
            client_id_api,
            chave_api,
            banco
        FROM contas_bancarias
        WHERE business_id = :business_id
        ORDER BY autodata ASC
        LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo
        ]);
        $contaBancaria = $stmt->fetch();

        if(!isset($contaBancaria['conta_bancaria_codigo'])){
            throw new Exception("Nenhuma conta bancária encontrada.");
        }

        return new SaidaFronteiraContaBancaria(
            codigo: $contaBancaria['conta_bancaria_codigo'],
            nome: $contaBancaria['nome'],
            banco: $contaBancaria['banco'],
            ambiente: $contaBancaria['ambiente'] ?? 'Sandbox',
            chaveAPI: (string) $contaBancaria['chave_api'] ?? '',
            clientIDAPI: (string) ($contaBancaria['client_id_api'] ?? '')
        );
    }

    #[Override] public function buscarTodasAsContasBancarias(string $empresaCodigo): SaidaFronteiraTodasAsContasBancarias
    {
        $sql = "SELECT 
            conta_bancaria_codigo,
            nome,
            chave_api,
            ambiente,
            client_id_api,
            banco
        FROM contas_bancarias
        WHERE business_id = :business_id
        ORDER BY autodata ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo
        ]);
        $contasBancarias = $stmt->fetchAll();

        $saidaTodasAsContasBancarias = new SaidaFronteiraTodasAsContasBancarias();

        foreach($contasBancarias as $contaBancaria){
            $contaBancariaTemp = new ContaBancaria(
                contaBancariaCodigo: $contaBancaria['conta_bancaria_codigo'],
                nome: $contaBancaria['nome'],
                banco: $contaBancaria['banco'],
                ambiente: $contaBancaria['ambiente'] ?? 'Sandbox',
                chaveAPI: (string) ($contaBancaria['chave_api'] ?? ''),
                clientIDAPI: (string) ($contaBancaria['client_id_api'] ?? '')
            );

            $sql = "SELECT 
                evento_momento,
                evento_descricao
            FROM contas_bancarias_eventos
            WHERE business_id = :business_id AND conta_bancaria_codigo = :conta_bancaria_codigo
            ORDER BY evento_momento DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'business_id' => $empresaCodigo,
                'conta_bancaria_codigo' => $contaBancaria['conta_bancaria_codigo']
            ]);

            $eventos = $stmt->fetchAll();
            foreach($eventos as $evento){
                $contaBancariaTemp->adicionarEvento(
                    momento: $evento['evento_momento'],
                    descricao: $evento['evento_descricao']
                );
            }

            $saidaTodasAsContasBancarias->adicionarContaBancaria($contaBancariaTemp);
        }

        return $saidaTodasAsContasBancarias;
    }

    #[Override] public function criarPrimeiraContaBancaria(string $empresaCodigo, string $contaBancariaCodigo, string $nome, string $banco): void
    {

        $sql = "INSERT INTO contas_bancarias
        (
            business_id,
            conta_bancaria_codigo,
            nome,
            banco,
            autodata
        )
        VALUES
        (
            :business_id,
            :conta_bancaria_codigo,
            :nome,
            :banco,
            :agora
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo,
            'banco' => $banco,
            'nome' => $nome,
            'agora' => date('Y-m-d H:i:s')
        ]);
    }

    #[Override] public function verificaAuthenticidadeWebhookAsaas(string $contaBancariaCodigo, string $empresaCodigo, string $webhookCodigo): bool
    {
        $sql = "SELECT webhook_codigo FROM contas_bancarias WHERE business_id = :business_id AND conta_bancaria_codigo = :conta_bancaria_codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo
        ]);
        $webhook = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($webhook['webhook_codigo']) and $webhook['webhook_codigo'] === $webhookCodigo;
    }

    #[Override] public function existeWebhookConfiguradoParaConta(string $contaBancariaCodigo, string $empresaCodigo): bool
    {

        $sql = "SELECT webhook_codigo FROM contas_bancarias WHERE business_id = :business_id AND conta_bancaria_codigo = :conta_bancaria_codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo
        ]);
        $webhook = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($webhook['webhook_codigo']) and !empty($webhook['webhook_codigo']) and $webhook['webhook_codigo'] !== '';
    }

    #[Override] public function atualizarOWebhookCodigoDaContaBancaria(string $contaBancariaCodigo, string $webhookCodigo, string $empresaCodigo): void
    {
        $sql = "UPDATE contas_bancarias SET webhook_codigo = :webhook_codigo WHERE business_id = :business_id AND conta_bancaria_codigo = :conta_bancaria_codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'webhook_codigo' => $webhookCodigo,
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo
        ]);
    }

    #[Override] public function novoEvento(string $contaBancariaCodigo, string $empresaCodigo, string $eventoDescricao): void
    {
        $sql = "INSERT INTO contas_bancarias_eventos
        (
            business_id,
            conta_bancaria_codigo,
            evento_momento,
            evento_descricao
        )
        VALUES
        (
            :business_id,
            :conta_bancaria_codigo,
            :evento_momento,
            :evento_descricao
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'conta_bancaria_codigo' => $contaBancariaCodigo,
            'evento_momento' => date('Y-m-d H:i:s'),
            'evento_descricao' => $eventoDescricao
        ]);
    }

    #[Override] public function atualizarContaBancaria(EntradaFronteiraAtualizarContaBancaria $parametros): void
    {

        $sql = "UPDATE contas_bancarias
        SET
            nome = :nome,
            chave_api = :chave_api,
            ambiente = :ambiente,
            client_id_api = :client_id_api,
            banco = :banco,
            data_ultima_atualizacao = :agora
        WHERE
            business_id = :business_id
        AND
            conta_bancaria_codigo = :conta_bancaria_codigo";
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'business_id' => $parametros->empresaCodigo,
            'ambiente' => $parametros->ambiente,
            'conta_bancaria_codigo' => $parametros->contaBancariaCodigo,
            'nome' => $parametros->nome,
            'chave_api' => $parametros->chaveAPI,
            'client_id_api' => $parametros->clientIDAPI,
            'banco' => $parametros->banco,
            'agora' => date('Y-m-d H:i:s')
        ]);
    }
}
