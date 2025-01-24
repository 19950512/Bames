<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Empresa\Fronteiras;

final readonly class SaidaFronteiraBuscarUsuarioPorCodigo
{

    public function __construct(
        public string $empresaCodigo,
        public string $contaCodigo,
        public string $nomeCompleto,
        public string $documento,
        public string $email,
        public string $hashSenha,
        public string $oab,
        public bool $diretorGeral = false,
        public bool $emailVerificado = false,
        public string $tokenParaRecuperarSenha = '',
    ){}
}
