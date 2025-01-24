<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Autenticacao;

use Override;
use PDO;
use Exception;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaConta;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

readonly class ImplementacaoRepositorioAutenticacao implements RepositorioAutenticacao
{

    private const MENSAGEM_ERRO_QUERY = 'Ocorreu um erro ao executar a consulta.';

    private const EXISTS_ACCOUNT_BY_EMAIL_AND_PASSWORD = "SELECT acc_id FROM accounts WHERE acc_email = :acc_email AND acc_password = :acc_password";
    private const EXISTS_ACCOUNT_BY_EMAIL = "SELECT acc_id FROM accounts WHERE acc_email = :acc_email";
    private const GET_ACCOUNT = "SELECT 
            acc_id,
            acc_nickname,
            business_id,
            acc_documento,
            token_recuperar_senha,
            acc_email_verificado,
            acc_diretor_geral,
            oab,
            acc_password,
            acc_email
        FROM accounts
        WHERE %s = :value";
    
    private const CREATE_ACCOUNT = "INSERT INTO accounts (
            acc_id,
            business_id,
            acc_nickname,
            acc_email,
            acc_documento,
            acc_password,
            acc_email_token_verificacao,
            acc_email_verificado,
            oab,
            acc_diretor_geral
        ) VALUES (
            :acc_id,
            :business_id,
            :acc_nickname,
            :acc_email,
            :acc_documento,
            :acc_password,
            :acc_email_token_verificacao,
            :acc_email_verificado,
            :oab,
            :acc_diretor_geral
        )";
    
    private const CREATE_BUSINESS = "INSERT INTO businesses (
            business_id,
            business_name,
            business_document,
            business_email
        ) VALUES (
            :business_id,
            :business_name,
            :business_document,
            :business_email
        )";

    public function __construct(
        private PDO $pdo,
    ){}


    #[Override] public function obterEmpresaPorCodigo(string $empresaCodigo): SaidaFronteiraEmpresa
    {

        if (empty($empresaCodigo)) {
//            $this->log->log(
//                level: Level::ERROR,
//                message: "O ID da empresa é obrigatório. ($empresaCodigo)"
//            );
            throw new Exception('O ID da empresa é obrigatório.');
        }

        try {

            $sql = $this->pdo->prepare("SELECT business_acesso_nao_autorizado_motivo, business_acesso_nao_autorizado, business_acesso_total_autorizado, business_creditos_saldo, business_id, business_document, business_name FROM businesses WHERE business_id = :business_id");
            $sql->bindValue(':business_id', $empresaCodigo);
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            if(!isset($fetch['business_id'])){
                throw new Exception("A empresa $empresaCodigo, não existe na base de dados.");
            }

            $queryResponsavel = "SELECT 
                acc_id,
                acc_nickname,
                acc_email,
                oab
            FROM accounts
            WHERE business_id = :business_id AND acc_diretor_geral = true";
            
            $sqlResponsavel = $this->pdo->prepare($queryResponsavel);
            $sqlResponsavel->execute([
                ':business_id' => $fetch['business_id']
            ]);

            $fetchResponsavel = $sqlResponsavel->fetch(PDO::FETCH_ASSOC);

            return new SaidaFronteiraEmpresa(
                empresaCodigo: $fetch['business_id'] ?? '',
                nome: $fetch['business_name'] ?? '',
                numeroDocumento: $fetch['business_document'] ?? '',

                responsavelCodigo: $fetchResponsavel['acc_id'] ?? '',
                responsavelOAB: $fetchResponsavel['oab'] ?? '',
                responsavelNomeCompleto: $fetchResponsavel['acc_nickname'] ?? '',
                responsavelEmail: $fetchResponsavel['acc_email'] ?? '',
                acessoNaoAutorizado: (bool) ($fetch['business_acesso_nao_autorizado'] ?? false),
                acessoNaoAutorizadoMotivo: (string) $fetch['business_acesso_nao_autorizado_motivo'] ?? '',
                acessoTotalAutorizadoPorMatheusMaydana: $fetch['business_acesso_total_autorizado'] ?? false,
                creditoSaldos: (float) ($fetch['business_creditos_saldo'] ?? 0),
            );

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception(self::MENSAGEM_ERRO_QUERY . $e->getMessage());
        }
    }

    #[Override] public function buscarToken(string $token, string $contaCodigo, string $empresaCodigo): string
    {

            if (empty($token) || empty($contaCodigo)) {
//                $this->log->log(
//                    level: Level::ERROR,
//                    message: "O token e o ID da conta são obrigatórios. ($token, $contaCodigo)"
//                );
                throw new Exception('O token e o ID da conta são obrigatórios.');
            }

            try {

                $sql = $this->pdo->prepare("SELECT token FROM auth_jwtokens WHERE token = :token AND acc_id = :acc_id AND business_id = :business_id");
                $sql->execute([
                    ':token' => $token,
                    ':acc_id' => $contaCodigo,
                    ':business_id' => $empresaCodigo
                ]);
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);

                if(!isset($fetch['token'])){
                    throw new Exception("O token $token, não existe na base de dados.");
                }

                return $fetch['token'];

            } catch (Exception $e) {

//                $this->log->log(
//                    level: Level::CRITICAL,
//                    message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//                );
                throw new Exception(self::MENSAGEM_ERRO_QUERY . $e->getMessage());
            }
    }

    #[Override] public function novoToken(string $token, string $contaCodigo, string $empresaCodigo): void
    {

            if (empty($token) || empty($contaCodigo)) {
//                $this->log->log(
//                    level: Level::ERROR,
//                    message: "O token e o ID da conta são obrigatórios. ($token, $contaCodigo)"
//                );
                throw new Exception('O token e o ID da conta são obrigatórios.');
            }

            try {

                $agora = date('Y-m-d H:i:s');
                $sql = $this->pdo->prepare("INSERT INTO auth_jwtokens (token, acc_id, business_id, autodata) VALUES (:token, :acc_id, :business_id, :agora)");
                $sql->execute([
                    ':token' => $token,
                    ':acc_id' => $contaCodigo,
                    ':business_id' => $empresaCodigo,
                    ':agora' => $agora
                ]);

            } catch (Exception $e) {

//                $this->log->log(
//                    level: Level::CRITICAL,
//                    message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//                );
                throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
            }
    }

    #[Override] public function salvaTokenParaRecuperacaoDeSenha(string $tokenRecuperarSenha, string $empresaCodigo, string $contaCodigo, string $contaEmail): void
    {

        if (empty($contaCodigo)) {
            throw new Exception('O ID da conta é obrigatório.');
        }

        try {

            $sql = $this->pdo->prepare("UPDATE accounts SET token_recuperar_senha = :token_recuperar_senha WHERE acc_id = :contaCodigo AND business_id = :business_id AND acc_email = :acc_email");
            $sql->execute([
                ':token_recuperar_senha' => $tokenRecuperarSenha,
                ':contaCodigo' => $contaCodigo,
                ':business_id' => $empresaCodigo,
                ':acc_email' => $contaEmail
            ]);

        } catch (Exception $e) {
            throw new Exception('Erro ao salvar o token para recuperação.');
        }

    }

    #[Override] public function cadastrarNovaEmpresa(EntradaFronteiraNovaEmpresa $params): void
    {

        if (empty($params->apelido)) {
//            $this->log->log(
//                level: Level::ERROR,
//                message: "O nome da empresa é obrigatório. ($params->nome)"
//            );
            throw new Exception('O nome da empresa é obrigatório.');
        }

        try {

            $sql = $this->pdo->prepare(self::CREATE_BUSINESS);
            $sql->execute([
                ':business_id' => $params->empresaCodigo,
                ':business_name' => $params->apelido,
                ':business_document' => $params->numeroDocumento,
                ':business_email' => $params->responsavelEmail
            ]);

        } catch (Exception $e) {
//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function contaExistePorEmailESenha(string $email, string $senha): bool
    {

        if (empty($email) || empty($senha)) {
//            $this->log->log(
//                level: Level::ERROR,
//                message: "O e-mail e a senha são obrigatórios. ({$email}, {$senha}))"
//            );
            throw new Exception('O e-mail e a senha são obrigatórios.');
        }

        try {
            
            $sql = $this->pdo->prepare(self::EXISTS_ACCOUNT_BY_EMAIL_AND_PASSWORD);
            $sql->bindValue(':acc_email', $email);
            $sql->bindValue(':acc_password', $senha);
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            return isset($fetch['acc_id']) and !empty($fetch['acc_id']);
            
        } catch (Exception $e) {
//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function jaExisteUmaContaComEstaOAB(string $oab): bool
    {
        if (!empty($oab)) {
            try {
                $sql = $this->pdo->prepare("SELECT acc_id FROM accounts WHERE oab = :oab");
                $sql->bindValue(':oab', $oab);
                $sql->execute();
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);

                return isset($fetch['acc_id']) and !empty($fetch['acc_id']);

            } catch (Exception $e) {
                throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
            }
        }

        return false;
    }
    #[Override] public function jaExisteContaComEsseEmail(string $email): bool
    {

        if (empty($email)) {
//            $this->log->log(
//                level: Level::ERROR,
//                message: "O e-mail é obrigatório. ({$email})"
//            );
            throw new Exception('O e-mail é obrigatório.');
        }

        try {
            
            $sql = $this->pdo->prepare(self::EXISTS_ACCOUNT_BY_EMAIL);
            $sql->bindValue(':acc_email', $email);
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            return isset($fetch['acc_id']) and !empty($fetch['acc_id']);

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function empresaExistePorCodigo(string $empresaCodigo): bool
    {

            if (empty($empresaCodigo)) {
//                $this->log->log(
//                    level: Level::ERROR,
//                    message: "O ID da empresa é obrigatório. ({$empresaCodigo})"
//                );
                throw new Exception('O ID da empresa é obrigatório.');
            }

            try {

                $sql = $this->pdo->prepare("SELECT business_id FROM businesses WHERE business_id = :business_id");
                $sql->bindValue(':business_id', $empresaCodigo);
                $sql->execute();
                $fetch = $sql->fetch(PDO::FETCH_ASSOC);

                return isset($fetch['business_id']) and !empty($fetch['business_id']);

            } catch (Exception $e) {

//                $this->log->log(
//                    level: Level::CRITICAL,
//                    message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//                );
                throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
            }
    }

    #[Override] public function jaExisteEmpresaComEsseDocumento(string $numeroDocumento): bool
    {
        if (empty($numeroDocumento)){
            throw new Exception('O número do documento é obrigatório.');
        }

        try {
            $sql = $this->pdo->prepare("SELECT business_id FROM businesses WHERE business_document = :business_document");
            $sql->bindValue(':business_document', $numeroDocumento);
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            return isset($fetch['business_id']) and !empty($fetch['business_id']);

        } catch (Exception $e) {
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function obterOMotivoDoBloqueioDaConta(string $empresaCodigo, string $usuarioCodigo): string
    {

        try {

            $sql = $this->pdo->prepare('SELECT acc_acesso_bloqueado_mensagem FROM accounts WHERE acc_id = :acc_id AND business_id = :business_id');
            $sql->execute([
                'acc_id' => $usuarioCodigo,
                'business_id' => $empresaCodigo,
            ]);
            $mensagem = $sql->fetch(PDO::FETCH_ASSOC);

            return (string) ($mensagem['acc_acesso_bloqueado_mensagem'] ?? '');

        }catch (Exception $erro){
            return '';
        }
    }

    #[Override] public function atualizarOMotivoParaNaoAtivarAConta(string $empresaCodigo, string $usuarioCodigo, string $mensagem): void
    {
        try {

            $sql = $this->pdo->prepare('UPDATE accounts SET acc_acesso_bloqueado_mensagem = :mensagem WHERE acc_id = :acc_id AND business_id = :business_id');
            $sql->execute([
                'acc_id' => $usuarioCodigo,
                'business_id' => $empresaCodigo,
                'mensagem' => $mensagem
            ]);

        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }
    }

    #[Override] public function buscarTokenEmailVerificacao(string $empresaCodigo, string $usuarioCodigo): string
    {
        try {

            $sql = $this->pdo->prepare('SELECT acc_email_token_verificacao FROM accounts WHERE acc_id = :acc_id AND business_id = :business_id');
            $sql->execute([
                'acc_id' => $usuarioCodigo,
                'business_id' => $empresaCodigo
            ]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            if(isset($fetch['acc_email_token_verificacao']) AND !empty($fetch['acc_email_token_verificacao'])){
                return (string) $fetch['acc_email_token_verificacao'];
            }

            throw new Exception('Token de verificação não encontrado.');

        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }
    }

    #[Override] public function empresaJaFoiExecutadoPosCadastrar(string $empresaCodigo): bool
    {
        try {

            $sql = $this->pdo->prepare('SELECT business_pos_cadastrada_executado FROM accounts WHERE business_id = :business_id');
            $sql->execute([
                'business_id' => $empresaCodigo
            ]);
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            if(isset($fetch['business_pos_cadastrada_executado']) and $fetch['business_pos_cadastrada_executado'] == 1){
                return true;
            }

            return false;

        }catch (Exception $erro){
            return false;
        }
    }

    #[Override] public function buscarContaPorTokenDeVerificacaodeEmail(string $tokenVerificacaoEmail): SaidaFronteiraBuscarContaPorCodigo
    {

        if (empty($tokenVerificacaoEmail)) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "O ID é obrigatório. ({$tokenVerificacaoEmail})"
//            );
            throw new Exception('O ID é obrigatório.');
        }

        try {
            $account = $this->getAccount('acc_email_token_verificacao', $tokenVerificacaoEmail);
            return $account;

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "A conta não existe na base de dados com esse ID {$tokenVerificacaoEmail}."
//            );
            throw new Exception("A conta não existe na base de dados com esse Token de verificação de email $tokenVerificacaoEmail.");
        }
    }

    #[Override] public function posCadastrarEmpresaEfetuadaComSucesso(string $empresaCodigo): void
    {

        try {
            $sql = $this->pdo->prepare("UPDATE businesses SET business_pos_cadastrada_executado = 'true' WHERE business_id = :business_id");
            $sql->execute([
                ':business_id' => $empresaCodigo
            ]);

        } catch (Exception $e) {
            throw new Exception("Erro ao marcar a empresa como Pos Cadastrada Executada.");
        }
    }

    #[Override] public function buscarContaPorCodigo(string $contaCodigo): SaidaFronteiraBuscarContaPorCodigo
    {
        if (empty($contaCodigo)) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "O ID é obrigatório. ({$contaCodigo})"
//            );
            throw new Exception('O ID é obrigatório.');
        }

        try {
            $account = $this->getAccount('acc_id', $contaCodigo);
            return $account;

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "A conta não existe na base de dados com esse ID {$contaCodigo}."
//            );
            throw new Exception("A conta não existe na base de dados com esse ID $contaCodigo.");
        }
    }

    #[Override] public function adicionarBonusEmpresaRecemCadastrada(string $empresaCodigo, float $valorCreditos): void
    {
        try {
            $sql = $this->pdo->prepare("UPDATE businesses SET business_creditos_saldo = business_creditos_saldo + :valorCreditos WHERE business_id = :business_id");
            $sql->execute([
                ':valorCreditos' => $valorCreditos,
                ':business_id' => $empresaCodigo
            ]);

        } catch (Exception $e) {
            throw new Exception('Erro ao adicionar bônus.');
        }
    }

    #[Override] public function buscarContaPorTokenRecuperacaoDeSenha(string $tokenRecuperarSenha): SaidaFronteiraBuscarContaPorCodigo
    {

        try {
            $account = $this->getAccount('token_recuperar_senha', $tokenRecuperarSenha);
            return $account;

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "A conta não existe na base de dados com esse e-mail {$tokenRecuperarSenha}."
//            );

            throw new Exception("A conta não existe na base de dados com esse token recuperação de senha $tokenRecuperarSenha.");
        }
    }
    
    #[Override] public function atualizarSenhaDoUsuarioSistema(string $contaUsuarioHASHSenha, string $contaUsuarioCodigo, string $empresaCodigo): void
    {

        try {
            $sql = $this->pdo->prepare("UPDATE accounts SET acc_password = :acc_password, token_recuperar_senha = NULL WHERE acc_id = :acc_id AND business_id = :business_id");
            $sql->execute([
                ':acc_password' => $contaUsuarioHASHSenha,
                ':acc_id' => $contaUsuarioCodigo,
                ':business_id' => $empresaCodigo
            ]);

        } catch (Exception $e) {
            throw new Exception('Erro ao atualizar a senha.');

        }catch (Exception $e) {
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function buscarContaPorEmail(string $email): SaidaFronteiraBuscarContaPorCodigo
    {
        try {
            $account = $this->getAccount('acc_email', $email);
            return $account;

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::ERROR,
//                message: "A conta não existe na base de dados com esse e-mail {$email}."
//            );

            throw new Exception("A conta não existe na base de dados com esse e-mail $email.");
        }
    }

    #[Override] public function novaConta(EntradaFronteiraNovaConta $params): void
    {

        if (empty($params->nomeCompleto) || empty($params->email) || empty($params->senha)) {
//            $this->log->log(
//                level: Level::ERROR,
//                message: "O nome completo, o e-mail e a senha são obrigatórios. ({$params->nomeCompleto}, {$params->email}, {$params->senha})"
//            );
            throw new Exception('O nome completo, o e-mail e a senha são obrigatórios.');
        }
     
        try {

            $sql = $this->pdo->prepare(self::CREATE_ACCOUNT);
            $sql->execute([
                ':acc_id' => $params->contaCodigo,
                ':business_id' => $params->empresaCodigo,
                ':acc_nickname' => $params->nomeCompleto,
                ':acc_email' => $params->email,
                ':acc_documento' => $params->documento,
                ':acc_password' => $params->senha,
                ':acc_email_token_verificacao' => $params->tokenValidacaoEmail,
                ':acc_email_verificado' => 'false',
                ':oab' => $params->oab,
                ':acc_diretor_geral' => $params->diretorGeral,
            ]);
            

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }

    #[Override] public function oFCMTokenJaEstaCadastrado(string $entidadeEmpresarial, string $usuarioCodigo, string $FCMToken): bool
    {

        $sql = $this->pdo->prepare("SELECT codigo FROM accounts_fcm_tokens WHERE business_id = :business_id AND acc_id = :acc_id AND fcm_token = :fcm_token");
        $sql->execute([
            ':business_id' => $entidadeEmpresarial,
            ':acc_id' => $usuarioCodigo,
            ':fcm_token' => $FCMToken
        ]);

        $fetch = $sql->fetch(PDO::FETCH_ASSOC);

        return isset($fetch['codigo']) and !empty($fetch['codigo']);
    }

    #[Override] public function marcarEmailVerificado(string $usuarioCodigo): void
    {
        $sql = $this->pdo->prepare("UPDATE accounts SET acc_email_token_verificacao = NULL, acc_email_verificado = 'true' WHERE acc_id = :acc_id");
        $sql->execute([
            ':acc_id' => $usuarioCodigo
        ]);
    }

    #[Override] public function removerFCMTokenInvalido(string $businessId, string $FCMToken): void
    {
        /*
         Nao implementar DELETE, somente UPDATE para desativar o token
        $sql = $this->pdo->prepare("DELETE FROM accounts_fcm_tokens WHERE business_id = :business_id AND fcm_token = :fcm_token");
        $sql->execute([
            ':business_id' => $businessId,
            ':fcm_token' => $FCMToken
        ]);
        */

        $sql = $this->pdo->prepare("UPDATE accounts_fcm_tokens SET fcm_token = :novoFCMToken WHERE business_id = :business_id AND fcm_token = :fcm_token");
        $sql->execute([
            ':novoFCMToken' => 'DELETED_'.$FCMToken,
            ':business_id' => $businessId,
            ':fcm_token' => $FCMToken
        ]);
    }
    #[Override] public function salvarNovoFCMToken(string $entidadeEmpresarial, string $usuarioCodigo, string $FCMToken): void
    {
        $sql = $this->pdo->prepare("INSERT INTO accounts_fcm_tokens (business_id, acc_id, fcm_token, momento) VALUES (:business_id, :acc_id, :fcm_token, :momento)");
        $sql->execute([
            ':business_id' => $entidadeEmpresarial,
            ':acc_id' => $usuarioCodigo,
            ':fcm_token' => $FCMToken,
            ':momento' => date('Y-m-d H:i:s')
        ]);
    }

    private function getAccount(string $field, string $value): SaidaFronteiraBuscarContaPorCodigo
    {
        $allowedFields = ['acc_id', 'acc_email', 'token_recuperar_senha', 'acc_email_token_verificacao'];

        if (!in_array($field, $allowedFields)) {
            throw new Exception('Campo inválido.');
        }

        try {

            $sql = $this->pdo->prepare(sprintf(self::GET_ACCOUNT, $field));
            $sql->bindValue(':value', $value);
            $sql->execute();
            $fetch = $sql->fetch(PDO::FETCH_ASSOC);

            if(!isset($fetch['acc_id'])){
                throw new Exception("A conta $value, não existe na base de dados.");
            }

            return new SaidaFronteiraBuscarContaPorCodigo(
                empresaCodigo: $fetch['business_id'],
                contaCodigo: $fetch['acc_id'],
                nomeCompleto: $fetch['acc_nickname'],
                email: $fetch['acc_email'],
                documento: (string) ($fetch['acc_documento'] ?? ''),
                hashSenha: $fetch['acc_password'],
                oab: $fetch['oab'] ?? '',
                diretorGeral: $fetch['acc_diretor_geral'] == 1,
                emailVerificado: $fetch['acc_email_verificado'] == 1,
                tokenParaRecuperarSenha: $fetch['token_recuperar_senha'] ?? ''
            );

        } catch (Exception $e) {

//            $this->log->log(
//                level: Level::CRITICAL,
//                message: "Ocorreu um erro ao executar a consulta ".__FUNCTION__.": {$e->getMessage()}"
//            );
            throw new Exception(self::MENSAGEM_ERRO_QUERY.$e->getMessage());
        }
    }
}