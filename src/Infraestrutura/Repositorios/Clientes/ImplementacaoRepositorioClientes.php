<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Clientes;

use App\Dominio\Repositorios\Clientes\Fronteiras\ClienteInformacoesBasicas;
use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraAtualizarInformacoesDoCliente;
use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraCadastrarNovoCliente;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClientes;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use Exception;
use Override;
use PDO;

class ImplementacaoRepositorioClientes implements RepositorioClientes
{

    const string CLIENTE_SELECT = 'SELECT
            pessoa_codigo,
            pessoa_nome,
            pessoa_documento,
            pessoa_tipo,
            pessoa_email,
            pessoa_telefone,
            pessoa_whatsapp,
            pessoa_endereco,
            pessoa_endereco_numero,
            pessoa_endereco_complemento,
            pessoa_endereco_bairro,
            pessoa_endereco_cidade,
            pessoa_endereco_estado,
            pessoa_endereco_cep,
            pessoa_nome_da_mae,
            pessoa_cpf_da_mae,
            pessoa_data_nascimento,
            pessoa_familiares,
            pessoa_nome_pai,
            pessoa_cpf_pai,
            pessoa_rg,
            pessoa_pis,
            pessoa_carteira_trabalho,
            pessoa_telefones,
            pessoa_emails,
            pessoa_enderecos,
            pessoa_sexo
        FROM pessoas
        WHERE business_id = :business_id';
    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function jaExisteUmClienteComEsteEmailOuDocumento(string $email, string $documento, string $empresaCodigo, string $clienteCodigo): bool
    {
        $pdo = $this->pdo->prepare("SELECT
                pessoa_codigo
            FROM pessoas
            WHERE business_id = :business_id
            AND pessoa_codigo != :pessoa_codigo
            AND ((pessoa_email <> '' AND pessoa_email = :pessoa_email) OR pessoa_documento = :pessoa_documento)
        ");
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'pessoa_email' => $email,
            'pessoa_codigo' => $clienteCodigo,
            'pessoa_documento' => $documento
        ]);
        $cliente = $pdo->fetch(PDO::FETCH_ASSOC);

        return isset($cliente['pessoa_codigo']) and !empty($cliente['pessoa_codigo']);
    }

    #[Override] public function getTodosClientes(string $empresaCodigo): SaidaFronteiraClientes
    {
        $pdo = $this->pdo->prepare("SELECT
                pessoa_codigo,
                pessoa_nome,
                pessoa_documento,
                pessoa_whatsapp
            FROM pessoas
            WHERE business_id = :business_id AND pessoa_e_cliente = 'true'
            ORDER BY pessoa_nome ASC
        ");
        $pdo->execute([
            'business_id' => $empresaCodigo
        ]);
        $clientes = $pdo->fetchAll(PDO::FETCH_ASSOC);

        $clientesRetorno = new SaidaFronteiraClientes();

        foreach($clientes as $cliente){

            $clienteTemp = new ClienteInformacoesBasicas(
                codigo: $cliente['pessoa_codigo'],
                nomeCompleto: $cliente['pessoa_nome'],
                documento: $cliente['pessoa_documento'],
                whatsapp: $cliente['pessoa_whatsapp'] ?? '',
            );

            $clientesRetorno->add($clienteTemp);
        }

        return $clientesRetorno;
    }

    #[Override] public function atualizarInformacoesDoCliente(EntradaFronteiraAtualizarInformacoesDoCliente $parametros): void
    {
        $pdo = $this->pdo->prepare('UPDATE pessoas SET
                pessoa_nome = :pessoa_nome,
                pessoa_email = :pessoa_email,
                pessoa_telefone = :pessoa_telefone,
                pessoa_documento = :pessoa_documento,
                pessoa_data_nascimento = :pessoa_data_nascimento,
                pessoa_endereco = :pessoa_endereco,
                pessoa_endereco_numero = :pessoa_endereco_numero,
                pessoa_endereco_complemento = :pessoa_endereco_complemento,
                pessoa_endereco_bairro = :pessoa_endereco_bairro,
                pessoa_endereco_cidade = :pessoa_endereco_cidade,
                pessoa_endereco_estado = :pessoa_endereco_estado,
                pessoa_endereco_cep = :pessoa_endereco_cep,
                pessoa_nome_da_mae = :pessoa_nome_da_mae,
                pessoa_cpf_da_mae = :pessoa_cpf_da_mae,
                pessoa_sexo = :pessoa_sexo,
                pessoa_familiares = :pessoa_familiares,
                pessoa_nome_pai = :pessoa_nome_pai,
                pessoa_cpf_pai = :pessoa_cpf_pai,
                pessoa_rg = :pessoa_rg,
                pessoa_pis = :pessoa_pis,
                pessoa_carteira_trabalho = :pessoa_carteira_trabalho,
                pessoa_telefones = :pessoa_telefones,
                pessoa_emails = :pessoa_emails,
            pessoa_enderecos = :pessoa_enderecos
            WHERE business_id = :business_id
            AND pessoa_codigo = :pessoa_codigo
        ');
        $pdo->execute([
            'pessoa_nome' => $parametros->nomeCompleto,
            'pessoa_email' => $parametros->email,
            'pessoa_telefone' => $parametros->telefone,
            'pessoa_documento' => $parametros->documento,
            'business_id' => $parametros->empresaCodigo,
            'pessoa_codigo' => $parametros->codigoCliente,
            'pessoa_data_nascimento' => $parametros->dataNascimento,
            'pessoa_endereco' => $parametros->endereco,
            'pessoa_endereco_numero' => $parametros->enderecoNumero,
            'pessoa_endereco_complemento' => $parametros->enderecoComplemento,
            'pessoa_endereco_bairro' => $parametros->bairro,
            'pessoa_endereco_cidade' => $parametros->cidade,
            'pessoa_endereco_estado' => $parametros->estado,
            'pessoa_endereco_cep' => $parametros->cep,
            'pessoa_nome_da_mae' => $parametros->nomeDaMae,
            'pessoa_cpf_da_mae' => $parametros->cpfMae,
            'pessoa_sexo' => $parametros->sexo,
            'pessoa_nome_pai' => $parametros->nomeDoPai,
            'pessoa_cpf_pai' => $parametros->cpfPai,
            'pessoa_rg' => $parametros->rg,
            'pessoa_pis' => $parametros->pis,
            'pessoa_carteira_trabalho' => $parametros->carteiraTrabalho,
            'pessoa_telefones' => json_encode($parametros->telefones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'pessoa_emails' => json_encode($parametros->emails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'pessoa_enderecos' => json_encode($parametros->enderecos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'pessoa_familiares' => json_encode($parametros->familiares, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    #[Override] public function jaExisteUmClienteComEsteDocumento(string $documento, string $empresaCodigo): bool
    {
        $pdo = $this->pdo->prepare('SELECT
                pessoa_codigo
            FROM pessoas
            WHERE business_id = :business_id
            AND pessoa_documento = :pessoa_documento
        ');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'pessoa_documento' => $documento
        ]);
        $cliente = $pdo->fetch(PDO::FETCH_ASSOC);

        return isset($cliente['pessoa_codigo']) and !empty($cliente['pessoa_codigo']);
    }

    #[Override] public function buscarClientePorDocumento(string $documento, string $empresaCodigo): SaidaFronteiraClienteDetalhado
    {
        $pdo = $this->pdo->prepare( self::CLIENTE_SELECT .' AND pessoa_documento = :pessoa_documento');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'pessoa_documento' => $documento
        ]);
        $cliente = $pdo->fetch(PDO::FETCH_ASSOC);

        if(!isset($cliente['pessoa_codigo']) or empty($cliente['pessoa_codigo'])){
            throw new Exception('Cliente do documento '.$documento.' não encontrado.');
        }

        return $this->processSaidaClienteDetalhado($cliente);
    }

    #[Override] public function buscarClientePorCodigo(string $codigoCliente, string $empresaCodigo): SaidaFronteiraClienteDetalhado
    {
        $pdo = $this->pdo->prepare(self::CLIENTE_SELECT .' AND pessoa_codigo = :pessoa_codigo');
        $pdo->execute([
            'business_id' => $empresaCodigo,
            'pessoa_codigo' => $codigoCliente
        ]);
        $cliente = $pdo->fetch(PDO::FETCH_ASSOC);

        if(!isset($cliente['pessoa_codigo']) or empty($cliente['pessoa_codigo'])){
            throw new Exception('Cliente do codigo '.$codigoCliente.' não encontrado.');
        }

        return $this->processSaidaClienteDetalhado($cliente);
    }

    #[Override] public function cadastrarNovoCliente(EntradaFronteiraCadastrarNovoCliente $parametros): void
    {
        $pdo = $this->pdo->prepare('INSERT INTO pessoas (
            pessoa_codigo,
            pessoa_nome,
            pessoa_documento,
            pessoa_tipo,
            pessoa_data_nascimento,
            pessoa_email,
            pessoa_telefone,
            pessoa_endereco,
            pessoa_endereco_numero,
            pessoa_endereco_bairro,
            pessoa_endereco_cidade,
            pessoa_endereco_estado,
            pessoa_endereco_cep,
            pessoa_nome_da_mae,
            pessoa_cpf_da_mae,
            pessoa_sexo,
            pessoa_familiares,
            pessoa_nome_pai,
            pessoa_cpf_pai,
            pessoa_rg,
            pessoa_pis,
            pessoa_carteira_trabalho,
            pessoa_telefones,
            pessoa_emails,
            pessoa_enderecos,
            business_id,
            pessoa_e_cliente
        ) VALUES (
            :pessoa_codigo,
            :pessoa_nome,
            :pessoa_documento,
            :pessoa_tipo,
            :pessoa_data_nascimento,
            :pessoa_email,
            :pessoa_telefone,
            :pessoa_endereco,
            :pessoa_endereco_numero,
            :pessoa_endereco_bairro,
            :pessoa_endereco_cidade,
            :pessoa_endereco_estado,
            :pessoa_endereco_cep,
            :pessoa_nome_da_mae,
            :pessoa_cpf_da_mae,
            :pessoa_sexo,
            :pessoa_familiares,
            :pessoa_nome_pai,
            :pessoa_cpf_pai,
            :pessoa_rg,
            :pessoa_pis,
            :pessoa_carteira_trabalho,
            :pessoa_telefones,
            :pessoa_emails,
            :pessoa_enderecos,
            :business_id,
            :pessoa_e_cliente
        )');
        $pdo->execute([
            'pessoa_codigo' => $parametros->clienteID,
            'pessoa_nome' => $parametros->nomeCompleto,
            'pessoa_documento' => $parametros->documento,
            'pessoa_data_nascimento' => $parametros->dataNascimento,
            'pessoa_tipo' => 'cliente',
            'pessoa_email' => $parametros->email,
            'pessoa_telefone' => $parametros->telefone,
            'business_id' => $parametros->empresaCodigo,
            'pessoa_endereco' => $parametros->logradouro,
            'pessoa_endereco_numero' => $parametros->numero,
            'pessoa_endereco_bairro' => $parametros->bairro,
            'pessoa_endereco_cidade' => $parametros->cidade,
            'pessoa_endereco_estado' => $parametros->estado,
            'pessoa_endereco_cep' => $parametros->cep,
            'pessoa_nome_da_mae' => $parametros->nomeMae,
            'pessoa_cpf_da_mae' => $parametros->cpfMae,
            'pessoa_sexo' => $parametros->sexo,
            'pessoa_familiares' => json_encode($parametros->familiares),
            'pessoa_nome_pai' => $parametros->nomePai,
            'pessoa_cpf_pai' => $parametros->cpfPai,
            'pessoa_rg' => $parametros->rg,
            'pessoa_pis' => $parametros->pis,
            'pessoa_carteira_trabalho' => $parametros->carteiraTrabalho,
            'pessoa_telefones' => json_encode($parametros->telefones),
            'pessoa_emails' => json_encode($parametros->emails),
            'pessoa_enderecos' => json_encode($parametros->enderecos),
            'pessoa_e_cliente' => $parametros->novoCliente ? 'true' : 'false'
        ]);
    }

    #[Override] public function salvarEventosDoCliente(string $codigoCliente, string $empresaCodigo, array $eventos): void
    {
        $pdo = $this->pdo->prepare('INSERT INTO pessoas_eventos (
            business_id,
            pessoa_codigo,
            evento_descricao,
            evento_momento
        ) VALUES (
            :business_id,
            :pessoa_codigo,
            :evento_descricao,
            :evento_momento
        )');
        foreach($eventos as $evento){
            $pdo->execute([
                'business_id' => $empresaCodigo,
                'pessoa_codigo' => $codigoCliente,
                'evento_descricao' => $evento['descricao'],
                'evento_momento' => $evento['momento']
            ]);
        }
    }

    private function processSaidaClienteDetalhado(array $cliente): SaidaFronteiraClienteDetalhado
    {

        $retorno = new SaidaFronteiraClienteDetalhado(
            codigo: $cliente['pessoa_codigo'],
            nomeCompleto: $cliente['pessoa_nome'],
            tipo: $cliente['pessoa_tipo'] ?? '',
            email: $cliente['pessoa_email'] ?? '',
            telefone: $cliente['pessoa_telefone'] ?? '',
            documento: $cliente['pessoa_documento'] ?? '',
            dataNascimento: $cliente['pessoa_data_nascimento'] ?? '',
            endereco: $cliente['pessoa_endereco'] ?? '',
            enderecoNumero: $cliente['pessoa_endereco_numero'] ?? '',
            enderecoComplemento: $cliente['pessoa_endereco_complemento'] ?? '',
            enderecoBairro: $cliente['pessoa_endereco_bairro'] ?? '',
            enderecoCidade: $cliente['pessoa_endereco_cidade'] ?? '',
            enderecoEstado: $cliente['pessoa_endereco_estado'] ?? '',
            enderecoCep: $cliente['pessoa_endereco_cep'] ?? '',
            nomeMae: $cliente['pessoa_nome_da_mae'] ?? '',
            cpfMae: $cliente['pessoa_cpf_da_mae'] ?? '',
            sexo: $cliente['pessoa_sexo'] ?? '',
            nomePai: $cliente['pessoa_nome_pai'] ?? '',
            cpfPai: $cliente['pessoa_cpf_pai'] ?? '',
            rg: $cliente['pessoa_rg'] ?? '',
            pis: $cliente['pessoa_pis'] ?? '',
            carteiraTrabalho: $cliente['pessoa_carteira_trabalho'] ?? '',
            telefones: $this->getArrayFromJSON($cliente['pessoa_telefones'] ?? ''),
            emails: $this->getArrayFromJSON($cliente['pessoa_emails'] ?? ''),
            enderecos: $this->getArrayFromJSON($cliente['pessoa_enderecos'] ?? ''),
            familiares: $this->getArrayFromJSON($cliente['pessoa_familiares'] ?? ''),
        );

        return $retorno;
    }

    private function getArrayFromJSON(string $json): array
    {
        return json_validate($json) ? json_decode($json, true) : [];
    }
}
