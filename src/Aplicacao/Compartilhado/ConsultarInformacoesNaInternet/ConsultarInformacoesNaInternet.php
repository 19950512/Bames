<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet;

use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarCPF;

interface ConsultarInformacoesNaInternet
{
    public function consultarCPF(string $cpf): SaidaFronteiraConsultarCPF;
}