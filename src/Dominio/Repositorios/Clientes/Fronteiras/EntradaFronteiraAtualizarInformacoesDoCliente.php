<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes\Fronteiras;

readonly final class EntradaFronteiraAtualizarInformacoesDoCliente
{
    public function __construct(
        public string $codigoCliente,
        public string $empresaCodigo,
        public string $email,
        public string $nomeCompleto,
        public string $telefone,
        public string $documento,
        public string $dataNascimento,
        public string $sexo,
        public string $nomeDaMae,
        public string $cpfMae,
        public string $endereco,
        public string $enderecoNumero,
        public string $enderecoComplemento,
        public string $bairro,
        public string $cidade,
        public string $estado,
        public string $cep,
        public string $nomeDoPai,
        public string $cpfPai,
        public string $rg,
        public string $pis,
        public string $carteiraTrabalho,
        public array $telefones,
        public array $familiares,
        public array $emails,
        public array $enderecos,
    ){}
}
