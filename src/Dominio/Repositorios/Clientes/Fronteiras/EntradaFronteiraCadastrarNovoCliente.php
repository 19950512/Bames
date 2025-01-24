<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\Fronteiras;

readonly final class EntradaFronteiraCadastrarNovoCliente
{
    public function __construct(
        public string $clienteID,
        public string $nomeCompleto,
        public string $email,
        public string $telefone,
        public string $documento,
        public string $empresaCodigo,
        public string $logradouro,
        public string $numero,
        public string $complemento,
        public string $bairro,
        public string $cidade,
        public string $estado,
        public string $cep,
        public string $nomeMae,
        public string $cpfMae,
        public string $sexo,
        public string $dataNascimento,
        public array $familiares,
        public string $nomePai,
        public string $cpfPai,
        public string $rg,
        public string $pis,
        public string $carteiraTrabalho,
        public array $telefones,
        public array $emails,
        public array $enderecos,
        public bool $novoCliente,
    ){}
}