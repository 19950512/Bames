<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Enums;

enum CustosInformacoesNaInternet: string
{

    case CONSULTA_DOCUMENTO = 'CONSULTA_DOCUMENTO';

    case CONSULTA_PROCESSOS_DOCUMENTO = 'CONSULTA_PROCESSOS_DOCUMENTO';

    case CONSULTA_OAB = 'CONSULTA_OAB';

    public function buscarCusto(): float
    {
        return match ($this) {
            self::CONSULTA_DOCUMENTO => 10,
            self::CONSULTA_OAB => 20,
            self::CONSULTA_PROCESSOS_DOCUMENTO => 30,
            default => 'Custo não encontrado',
        };
    }
}