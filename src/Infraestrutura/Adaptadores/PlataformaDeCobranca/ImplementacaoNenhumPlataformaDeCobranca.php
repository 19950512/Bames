<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\PlataformaDeCobranca;

use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraBaixarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConfigurarWebhook;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConsultarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraVerificarConexaoComPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\PlataformaDeCobranca;
use Exception;
use Override;

class ImplementacaoNenhumPlataformaDeCobranca implements PlataformaDeCobranca
{

    #[Override] public function emitirBoleto(EntradaFronteiraEmitirBoleto $parametros): SaidaFronteiraEmitirBoleto
    {
        throw new Exception("Ops, não foi possível emitir o boleto. - TODO Implementar.");
    }

    #[Override] public function consultarBoleto(EntradaFronteiraConsultarBoleto $parametros): SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma
    {
        throw new Exception("Ops, não foi possível consultar o boleto. - TODO Implementar.");
    }

    #[Override] public function conexaoEstabelecidaComSucessoComAPlataformaAPICobranca(EntradaFronteiraVerificarConexaoComPlataforma $parametros): true
    {
        return true;
    }

    #[Override] public function configurarWebhook(EntradaFronteiraConfigurarWebhook $parametros): void
    {
        throw new Exception("Ops, não foi possível configurar o webhook. - TODO Implementar.");
    }

    #[Override] public function baixarBoleto(EntradaFronteiraBaixarBoleto $parametros): void
    {
        throw new Exception("Ops, não foi possível baixar o boleto. - TODO Implementar.");
    }

    public function obterTodosOsBoletosDoParcelamento(EntradaObterTodosOsBoletosDoParcelamento $parametros): SaidaObterTodosOsBoletosDoParcelamento
    {
        throw new Exception("Ops, não foi possível obter os boletos do parcelamento. - TODO Implementar.");
    }
}
