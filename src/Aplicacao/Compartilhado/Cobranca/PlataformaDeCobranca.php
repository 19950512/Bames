<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cobranca;

use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraBaixarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConfigurarWebhook;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConsultarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraVerificarConexaoComPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaObterTodosOsBoletosDoParcelamento;

interface PlataformaDeCobranca
{
    public function emitirBoleto(EntradaFronteiraEmitirBoleto $parametros): SaidaFronteiraEmitirBoleto;

    public function consultarBoleto(EntradaFronteiraConsultarBoleto $parametros): SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma;

    public function obterTodosOsBoletosDoParcelamento(EntradaObterTodosOsBoletosDoParcelamento $parametros): SaidaObterTodosOsBoletosDoParcelamento;

    public function baixarBoleto(EntradaFronteiraBaixarBoleto $parametros): void;

    public function conexaoEstabelecidaComSucessoComAPlataformaAPICobranca(EntradaFronteiraVerificarConexaoComPlataforma $parametros): true;

    public function configurarWebhook(EntradaFronteiraConfigurarWebhook $parametros): void;
}