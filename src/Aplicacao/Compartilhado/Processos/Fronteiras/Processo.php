<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Processos\Fronteiras;

final class Processo
{
    public function __construct(
        public string $numero,
        public string $data,
        public string $situacao,
        public string $classe,
        public string $area,
        public string $assunto,
        public string $juiz,
        public string $origem,
        public string $comarca,
        public string $vara,
        public string $instancia,
        public string $tribunal,
        public string $uf,
        public string $parte,
        public string $advogado,
        public string $oab,
        public string $tipo,
        public string $nomeCompleto
    ){}
}