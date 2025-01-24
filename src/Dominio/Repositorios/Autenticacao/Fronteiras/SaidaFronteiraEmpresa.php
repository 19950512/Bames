<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Autenticacao\Fronteiras;

final readonly class SaidaFronteiraEmpresa
{
    public function __construct(
        public string $empresaCodigo,
        public string $nome,
	    public string $numeroDocumento,

        public string $responsavelCodigo,
        public string $responsavelOAB,
        public string $responsavelNomeCompleto,
        public string $responsavelEmail,
        public bool $acessoNaoAutorizado,
        public string $acessoNaoAutorizadoMotivo,
        public bool $acessoTotalAutorizadoPorMatheusMaydana = false,
        public float $creditoSaldos = 0,
        public array $colaboradores = [],
    ){}
}