<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Modelos;

use App\Dominio\Repositorios\Modelos\Fronteiras\Modelo;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraModelo;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraTodosModelos;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use Exception;
use PDO;

class ImplementacaoRepositorioModelos implements RepositorioModelos
{

    public function __construct(
        private PDO $pdo,
    ){}

    public function excluirModelo(string $modeloCodigo, string $empresaCodigo): void
    {
        $sql = "UPDATE modelos_documentos SET modelo_deleted = 'true' WHERE modelo_codigo = :modelo_codigo AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'modelo_codigo' => $modeloCodigo,
            'business_id' => $empresaCodigo,
        ]);
    }

    public function criarNovoModelo(string $modeloCodigo, string $nome, string $empresaCodigo): void
    {
        $sql = "INSERT INTO modelos_documentos (business_id, modelo_codigo, modelo_nome, modelo_momento, modelo_nome_arquivo) VALUES (:business_id, :modelo_codigo, :modelo_nome, :modelo_momento, :modelo_nome_arquivo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'modelo_codigo' => $modeloCodigo,
            'modelo_nome' => $nome,
            'modelo_nome_arquivo' => '',
            'modelo_momento' => date('Y-m-d H:i:s'),
        ]);
    }

    public function atualizarModelo(string $modeloCodigo, string $nome, string $empresaCodigo): void
    {
        $sql = "UPDATE modelos_documentos SET modelo_nome = :modelo_nome, modelo_momento = :modelo_momento WHERE modelo_codigo = :modelo_codigo AND business_id = :business_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'modelo_nome' => $nome,
                'modelo_momento' => date('Y-m-d H:i:s'),
                'modelo_codigo' => $modeloCodigo,
                'business_id' => $empresaCodigo,
            ]);
        }catch (Exception $e) {
            throw new Exception("Erro ao atualizar modelo: {$e->getMessage()}");
        }
    }

    public function obterModeloPorCodigo(string $modeloCodigo, string $empresaCodigo): SaidaFronteiraModelo
    {
        $sql = "SELECT modelo_codigo, modelo_nome, modelo_nome_arquivo FROM modelos_documentos WHERE modelo_codigo = :modelo_codigo AND business_id = :business_id AND modelo_deleted = 'false'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'modelo_codigo' => $modeloCodigo,
            'business_id' => $empresaCodigo,
        ]);
        $modelo = $stmt->fetch();

        return new SaidaFronteiraModelo(
            modeloCodigo: (string) $modelo['modelo_codigo'] ?? '',
            nome: (string) $modelo['modelo_nome'] ?? '',
            nomeArquivo: (string) $modelo['modelo_nome_arquivo'] ?? '',
        );
    }

    public function obterTodosOsModelos(string $empresaCodigo): SaidaFronteiraTodosModelos
    {
        $sql = "SELECT modelo_codigo, modelo_nome, modelo_nome_arquivo FROM modelos_documentos WHERE business_id = :business_id AND modelo_deleted = 'false'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
        ]);
        $modelos = $stmt->fetchAll();

        $saidaFronteiraTodosModelos = new SaidaFronteiraTodosModelos();
        foreach ($modelos as $modelo) {
            $saidaFronteiraTodosModelos->adicionarModelo(
                new Modelo(
                    codigo: $modelo['modelo_codigo'],
                    nome: $modelo['modelo_nome'],
                    nomeArquivo: $modelo['modelo_nome_arquivo'],
                )
            );
        }

        return $saidaFronteiraTodosModelos;
    }

    public function vincularArquivoAoModelo(string $modeloCodigo, string $empresaCodigo, string $arquivoNome): void
    {
        $sql = "UPDATE modelos_documentos SET modelo_nome_arquivo = :arquivo_nome WHERE modelo_codigo = :modelo_codigo AND business_id = :business_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'arquivo_nome' => $arquivoNome,
            'modelo_codigo' => $modeloCodigo,
            'business_id' => $empresaCodigo,
        ]);
    }

    public function salvarEvento(string $modeloCodigo, string $empresaCodigo, string $evento): void
    {
        $sql = "INSERT INTO modelos_documentos_eventos (business_id, modelo_codigo, evento_modelo_descricao, evento_modelo_momento) VALUES (:business_id, :modelo_codigo, :evento, :momento)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'modelo_codigo' => $modeloCodigo,
            'evento' => $evento,
            'momento' => date('Y-m-d H:i:s'),
        ]);
    }
}
