<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Fronteiras;

final readonly class SaidaFronteiraConsultarCPF
{
    public function __construct(
        public string $documento,
        public string $nomeCompleto,
        public string $dataNascimento,
        public string $rg,
        public string $pis,
        public string $carteiraTrabalho,
        public string $nomeMae,
        public string $cpfMae,
        public string $nomePai,
        public string $cpfPai,
        public array $familiares,
        public string $sexo,
        public array $telefones,
        public array $enderecos,
        public array $emails,
    ){}
}