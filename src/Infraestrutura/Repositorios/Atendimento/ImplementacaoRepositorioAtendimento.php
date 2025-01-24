<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Atendimento;

use PDO;
use Override;
use App\Dominio\Repositorios\Atendimento\RepositorioAtendimento;
use App\Dominio\Repositorios\Atendimento\Fronteiras\EntradaFronteiraNovoAtendimento;
use App\Dominio\Repositorios\Atendimento\Fronteiras\SaidaFronteiraBuscarAtendimento;

class ImplementacaoRepositorioAtendimento implements RepositorioAtendimento
{
    public function __construct(
        private PDO $pdo,
    ){}

    /*
    CREATE TABLE IF NOT EXISTS atendimentos
    (
        codigo serial NOT NULL,
        business_id character varying NOT NULL,
        cliente_id character varying NOT NULL,
        usuario_codigo_criou character varying COLLATE pg_catalog."default",
        atendimento_codigo character varying COLLATE pg_catalog."default",
        atendimento_etapa character varying COLLATE pg_catalog."default",
        atendimento_status character varying COLLATE pg_catalog."default",
        atendimento_descricao text COLLATE pg_catalog."default",
        atendimento_data_criacao character varying COLLATE pg_catalog."default",
        momento character varying,
        PRIMARY KEY (codigo)
    );
    */
    
    #[Override] public function novoAtendimento(EntradaFronteiraNovoAtendimento $parametros): void
    {
        $sql = "INSERT INTO atendimentos (business_id, cliente_id, usuario_codigo_criou, atendimento_codigo, atendimento_etapa, atendimento_status, atendimento_descricao, atendimento_data_criacao, momento) VALUES (:business_id, :cliente_id, :usuario_codigo_criou, :atendimento_codigo, :atendimento_etapa, :atendimento_status, :atendimento_descricao, :atendimento_data_criacao, :momento)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $parametros->empresaCodigo,
            'cliente_id' => $parametros->clienteCodigo,
            'usuario_codigo_criou' => $parametros->usuarioCodigo,
            'atendimento_codigo' => $parametros->atendimentoCodigo,
            'atendimento_etapa' => '1',
            'atendimento_status' => 'ABERTO',
            'atendimento_descricao' => $parametros->descricao,
            'atendimento_data_criacao' => date('Y-m-d H:i:s'),
            'momento' => date('Y-m-d H:i:s')
        ]);        
    }

    #[Override] public function obterAtendimento(string $empresaCodigo, string $atendimentoCodigo): SaidaFronteiraBuscarAtendimento
    {
        $sql = "SELECT
                business_id,
                cliente_id,
                atendimento_codigo,
                atendimento_descricao,
                atendimento_data_criacao,
                atendimento_status,
                usuario_codigo_criou
            FROM atendimentos
            WHERE business_id = :business_id AND atendimento_codigo = :codigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'business_id' => $empresaCodigo,
            'atendimento_codigo' => $atendimentoCodigo
        ]);
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);
        return new SaidaFronteiraBuscarAtendimento(
            empresaCodigo: (string) $atendimento['business_id'],
            clienteCodigo: (string) $atendimento['cliente_id'],
            descricao: (string) $atendimento['atendimento_descricao'],
            atendimentoCodigo: (string) $atendimento['atendimento_codigo'],
            dataInicio: (string) $atendimento['atendimento_data_criacao'],
            status: (string) $atendimento['atendimento_status'],
            usuarioCodigo: (string) $atendimento['usuario_codigo_criou']
        );
    }

}