<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Autenticacao\Fronteiras;

final readonly class SaidaFronteiraBuscarContaPorCodigo
{

    public function __construct(
        public string $empresaCodigo,
        public string $contaCodigo,
        public string $nomeCompleto,
        public string $email,
        public string $documento,
        public string $hashSenha,
        public string $oab,
        public bool $diretorGeral = false,
        public bool $emailVerificado = false,
        public string $tokenParaRecuperarSenha = '',
    ){}
}
