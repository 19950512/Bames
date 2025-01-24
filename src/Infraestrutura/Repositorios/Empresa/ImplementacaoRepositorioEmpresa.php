<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Empresa;

use PDO;
use Exception;
use PDOException;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Empresa\Fronteiras\EntradaFronteiraNovoColaborador;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraBuscarUsuarioPorCodigo;

readonly class ImplementacaoRepositorioEmpresa implements RepositorioEmpresa
{

    public function __construct(
        private PDO $pdo,
    ){}

    public function jaExisteUmUsuarioComEsseEmail(string $email): bool
    {
        try {

            $sql = $this->pdo->prepare("SELECT acc_id FROM accounts WHERE acc_email = :acc_email");
            $sql->execute([
                ':acc_email' => $email
            ]);

            $usuario = $sql->fetch(PDO::FETCH_ASSOC);

            return isset($usuario['acc_id']) and !empty($usuario['acc_id']);

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }

    public function buscarEmpresaPorCodigo(string $empresaCodigo): SaidaFronteiraEmpresa
    {

        try {

            $sql = $this->pdo->prepare("SELECT business_acesso_total_autorizado, business_creditos_saldo, business_id, business_name, business_document FROM businesses WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);

            $empresa = $sql->fetch(PDO::FETCH_ASSOC);

            if(!isset($empresa['business_id'])){
                throw new Exception('Empresa com ID '.$empresaCodigo.' não encontrada.');
            }

            $queryResponsavel = "SELECT 
                acc_id,
                oab,
                acc_nickname,
                acc_email
            FROM accounts
            WHERE business_id = :business_id AND acc_diretor_geral = true";
            
            $sqlResponsavel = $this->pdo->prepare($queryResponsavel);
            $sqlResponsavel->execute([
                ':business_id' => $empresa['business_id']
            ]);

            $fetchResponsavel = $sqlResponsavel->fetch(PDO::FETCH_ASSOC);

            return new SaidaFronteiraEmpresa(
                empresaCodigo: $empresa['business_id'],
                nome: $empresa['business_name'],
                numeroDocumento: $empresa['business_document'],

                responsavelCodigo: $fetchResponsavel['acc_id'] ?? '',
                responsavelOAB: $fetchResponsavel['oab'] ?? '',
                responsavelNomeCompleto: $fetchResponsavel['acc_nickname'] ?? '',
                responsavelEmail: $fetchResponsavel['acc_email'] ?? '',
                acessoNaoAutorizado: false,
                acessoNaoAutorizadoMotivo: '',
                creditoSaldos: (float) ($empresa['business_creditos_saldo'] ?? 0),
                acessoTotalAutorizadoPorMatheusMaydana: $empresa['business_acesso_total_autorizado'] ?? false,
            );

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }


    public function totalClientesDetalhado(): array
    {
        try {

            $sql = $this->pdo->prepare("SELECT 
                business_id,
                business_name,
                business_document,
                business_email,
                business_phone,
                business_whatsapp,
                business_address,
                business_address_number,
                business_address_complement,
                business_address_neighborhood,
                business_address_city,
                business_address_state,
                business_address_cep,
                business_creditos_saldo,
                business_acesso_total_autorizado,
                autodata
            FROM businesses");
            $sql->execute();

            return $sql->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }

    public function totalClientes(): int
    {
        try {

            $sql = $this->pdo->prepare("SELECT COUNT(business_id) as total FROM businesses");
            $sql->execute();

            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            return (int) $fetch['total'];

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }

    public function deletarTudoRelacionadoAEmpresa(string $empresaCodigo): void
    {
        try {

            $this->pdo->beginTransaction();

            $sql = $this->pdo->prepare("INSERT INTO accounts_deletados (
                    id,
                    acc_id,
                    business_id,
                    acc_email,
                    acc_diretor_geral,
                    acc_nickname,
                    token_recuperar_senha,
                    acc_password,
                    acc_created_at
                ) SELECT
                    id,
                    acc_id,
                    business_id,
                    acc_email,
                    acc_diretor_geral,
                    acc_nickname,
                    token_recuperar_senha,
                    acc_password,
                    acc_created_at
                FROM accounts WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);

            $sql = $this->pdo->prepare("DELETE FROM accounts WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);


            $sql = $this->pdo->prepare("INSERT INTO businesses_deletados (
                    id,
                    business_id,
                    business_name,
                    business_razao,
                    business_document,
                    business_email,
                    business_phone,
                    business_whatsapp,
                    business_address,
                    business_address_number,
                    business_address_complement,
                    business_address_neighborhood,
                    business_address_city,
                    business_address_state,
                    business_creditos_saldo,
                    business_address_cep,
                    business_acesso_total_autorizado,
                    autodata
                ) SELECT
                    id,
                    business_id,
                    business_name,
                    business_razao,
                    business_document,
                    business_email,
                    business_phone,
                    business_whatsapp,
                    business_address,
                    business_address_number,
                    business_address_complement,
                    business_address_neighborhood,
                    business_address_city,
                    business_address_state,
                    business_creditos_saldo,
                    business_address_cep,
                    business_acesso_total_autorizado,
                    autodata
                FROM businesses WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);
            $sql = $this->pdo->prepare("DELETE FROM businesses WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);
            
            $this->pdo->commit();

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            throw new Exception("Ocorreu um erro ao executar a consulta. - {$e->getMessage()}");
        }
    }

    public function buscarTodosUsuarios(string $empresaCodigo): array
    {
        try {

            $sql = $this->pdo->prepare("SELECT 
                acc_id,
                acc_nickname,
                acc_email
            FROM accounts
            WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);

            return $sql->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }

    public function buscarUsuarioPorCodigo(string $usuarioCodigo): SaidaFronteiraBuscarUsuarioPorCodigo
    {
        
        try {

            $sql = $this->pdo->prepare("SELECT 
                    acc_id,
                    acc_nickname,
                    business_id,
                    acc_documento,
                    acc_diretor_geral,
                    token_recuperar_senha,
                    oab,
                    acc_password,
                    acc_email
                FROM accounts
                WHERE acc_id = :acc_id"
            );
            $sql->execute([
                ':acc_id' => $usuarioCodigo,
            ]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            if(!isset($fetch['acc_id'])){
                throw new Exception("O usuário ID: $usuarioCodigo, não existe na base de dados.");
            }

            return new SaidaFronteiraBuscarUsuarioPorCodigo(
                empresaCodigo: $fetch['business_id'],
                contaCodigo: $fetch['acc_id'],
                nomeCompleto: $fetch['acc_nickname'],
                documento: $fetch['acc_documento'],
                email: $fetch['acc_email'],
                hashSenha: $fetch['acc_password'],
                oab: $fetch['oab'] ?? '',
                diretorGeral: $fetch['acc_diretor_geral'] == 1,
                tokenParaRecuperarSenha: $fetch['token_recuperar_senha'] ?? ''
            );

        } catch (PDOException $e) {

//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }

    public function novoColaborador(EntradaFronteiraNovoColaborador $params): void
    {

        if (empty($params->nomeCompleto) || empty($params->email)) {
            throw new Exception('O nome completo e o e-mail são obrigatórios.');
        }

        try {

            $sql = $this->pdo->prepare("INSERT INTO accounts (
                acc_id,
                business_id,
                acc_nickname,
                oab,
                acc_email
            ) VALUES (
                :acc_id,
                :business_id,
                :acc_nickname,
                :oab,
                :acc_email
            )");
            $sql->bindValue(':acc_id', $params->colaboradorCodigo);
            $sql->bindValue(':business_id', $params->empresaCodigo);
            $sql->bindValue(':acc_nickname', $params->nomeCompleto);
            $sql->bindValue(':oab', $params->oab);
            $sql->bindValue(':acc_email', $params->email);
            $sql->execute();

        } catch (PDOException $e) {

            throw new Exception("Ocorreu um erro ao executar a consulta.");
        }
    }
}