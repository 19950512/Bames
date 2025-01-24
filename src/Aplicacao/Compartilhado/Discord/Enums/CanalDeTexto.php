<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Discord\Enums;

use Exception;

enum CanalDeTexto: string
{
    case Webhook = 'webhook';

    case Exceptions = 'Exceptions';

    case Workers = 'Workers';

    case ConsultarProcessosPorOAB = 'ConsultarProcessosPorOAB';

    case ConsultarProcessosPorDocumento = 'ConsultarProcessosPorDocumento';

    case NovosClientes = 'NovosClientes';

    case Login = 'Login';

    case Clientes = 'Clientes';

    case ModelosDocumento = 'ModelosDocumento';

    case ClienteGerarDocumentoApartirDoModelo = 'ClienteGerarDocumentoApartirDoModelo';

    case Cobrancas = 'Cobrancas';

    case CobrancasAsaas = 'CobrancasAsaas';

    case BoletoConsultar = 'BoletoConsultar';
    case BoletoBaixar = 'BoletoBaixar';
    case BoletoLiquidarManualmente = 'BoletoLiquidarManualmente';
    case FinanceiroCaixa = 'FinanceiroCaixa';
    case BoletosSalvarParcelamento = 'BoletosSalvarParcelamento';
    case ContaBancariaVerificaIntegracao = 'ContaBancariaVerificaIntegracao';

    case NotificarAgenda = 'NotificarAgenda';

    case DocxToPDF = 'DocxToPDF';

    public function obterURL(): string
    {
        $pathWebhooks = __DIR__.'/../../Credenciais/url_webhooks_discord.php';
        if(!is_file($pathWebhooks)){
            throw new Exception('Arquivo de URLs de Webhook do Discord não encontrado');
        }

        $urls = include $pathWebhooks;

        return match($this) {
            self::Webhook => $urls['Webhook'],
            self::Exceptions => $urls['Exceptions'],
            self::ConsultarProcessosPorOAB => $urls['ConsultarProcessosPorOAB'],
            self::NovosClientes => $urls['NovosClientes'],
            self::Login => $urls['Login'],
            self::Clientes => $urls['Clientes'],
            self::ConsultarProcessosPorDocumento => $urls['ConsultarProcessosPorDocumento'],
            self::ModelosDocumento => $urls['ModelosDocumento'],
            self::ClienteGerarDocumentoApartirDoModelo => $urls['ClienteGerarDocumentoApartirDoModelo'],
            self::Cobrancas => $urls['Cobrancas'],
            self::CobrancasAsaas => $urls['CobrancasAsaas'],
            self::BoletoConsultar => $urls['BoletoConsultar'],
            self::BoletoBaixar => $urls['BoletoBaixar'],
            self::FinanceiroCaixa => $urls['FinanceiroCaixa'],
            self::BoletosSalvarParcelamento => $urls['BoletosSalvarParcelamento'],
            self::ContaBancariaVerificaIntegracao => $urls['ContaBancariaVerificaIntegracao'],
            self::DocxToPDF => $urls['DocxToPDF'],
            self::Workers => $urls['Workers'],
            self::NotificarAgenda => $urls['NotificarAgenda'],
        };
    }
}
