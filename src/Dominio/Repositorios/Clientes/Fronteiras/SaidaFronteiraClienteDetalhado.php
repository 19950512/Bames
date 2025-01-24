<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\Fronteiras;

final class SaidaFronteiraClienteDetalhado
{

    private array $eventos = [];
    private array $processos = [];

    public function __construct(
        public string $codigo,
        public string $nomeCompleto,
        public string $tipo,
        public string $email,
        public string $telefone,
        public string $documento,
        public string $dataNascimento,
        public string $endereco,
        public string $enderecoNumero,
        public string $enderecoComplemento,
        public string $enderecoBairro,
        public string $enderecoCidade,
        public string $enderecoEstado,
        public string $enderecoCep,
        public string $nomeMae,
        public string $cpfMae,
        public string $sexo,
        public string $nomePai,
        public string $cpfPai,
        public string $rg,
        public string $pis,
        public string $carteiraTrabalho,
        public array $telefones,
        public array $emails,
        public array $enderecos,
        public array $familiares
    ){}

    public function obterTelefones(): array
    {
        return array_map(function($telefone){
            return $telefone;
        }, $this->telefones);
    }

    public function obterEmails(): array
    {
        return array_map(function($email){
            return $email;
        }, $this->emails);
    }

    public function obterEnderecos(): array
    {
        return array_map(function($endereco){
            return [
                'logradouro' => $endereco['logradouro'] ?? '',
                'numero' => $endereco['numero'] ?? '',
                'complemento' => $endereco['complemento'] ?? '',
                'bairro' => $endereco['bairro'] ?? '',
                'cidade' => $endereco['cidade'] ?? '',
                'estado' => $endereco['estado'] ?? '',
                'cep' => $endereco['cep'] ?? '',
            ];
        }, $this->enderecos);
    }

    public function obterFamiliares(): array
    {
        return array_map(function($familiar){
            return [
                'nome' => $familiar['nome'],
                'parentesco' => $familiar['parentesco'],
                'documento' => $familiar['cpf'],
            ];
        }, $this->familiares);
    }

    public function obterEventos(): array
    {
        return $this->eventos;
    }

    public function obterProcessos(): array
    {
        return $this->processos;
    }

    public function adicionarProcesso(string $codigoCNJ, string $descricao): void
    {
        $this->processos[] = [
            'codigoCNJ' => $codigoCNJ,
            'descricao' => $descricao
        ];
    }

    public function adicionarEvento(string $momento, string $descricao): void
    {
        $this->eventos[] = [
            'momento' => date('d/m/Y', strtotime($momento)).' às '.date('H:i', strtotime($momento)),
            'descricao' => $descricao
        ];
    }
}