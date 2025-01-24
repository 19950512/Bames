<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Mensageria\Enumerados;

/*
    case EMAIL_EXCHANGE = 'email_exchange';
    case EMISSAO_COBRANCA_EXCHANGE = 'emissao_cobranca_exchange';
*/

enum TrocaMensagens: string
{
    case EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_EXCHAGE = 'empresa_recem_cadastrada_no_sistema_exchange';
    case EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_DLX_EXCHAGE = 'empresa_recem_cadastrada_no_sistema_dlx_exchange';

    case EMISSAO_BOLETO_EXCHANGE = 'emissao_boleto_exchange';
    case EMISSAO_BOLETO_DLX_EXCHANGE = 'emissao_boleto_dlq_exchange';


    case EMISSAO_NFSE_EXCHANGE = 'emissao_nfse_exchange';
    case EMISSAO_NFSE_DLX_EXCHANGE = 'emissao_nfse_dlq_exchange';


    case EMISSAO_EMAIL_EXCHANGE = 'emissao_email_exchange';
    case EMISSAO_EMAIL_DLX_EXCHANGE = 'emissao_email_dlq_exchange';

    case NOTIFICAR_COMPROMISSOS_EXCHANGE = 'notificar_compromissos_exchange';
    case NOTIFICAR_COMPROMISSOS_DLX_EXCHANGE = 'notificar_compromissos_dlq_exchange';

    case NOVO_EVENTO_AGENDA_EXCHANGE = 'novo_evento_agenda_exchange';
    case NOVO_EVENTO_AGENDA_DLX_EXCHANGE = 'novo_evento_agenda_dlq_exchange';

    static public function trocasMensagens(): array
    {
        return [
            // NOVO EVENTO AGENDA
            [
                'exchange' => self::NOVO_EVENTO_AGENDA_EXCHANGE,
                'type' => 'direct',
            ],
            [
                'exchange' => self::NOVO_EVENTO_AGENDA_DLX_EXCHANGE,
                'type'=> 'fanout',
            ],

            // EMPRESA RECEM CADASTRADA
            [
                'exchange' => self::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_EXCHAGE,
                'type' => 'direct',
            ],
            [
                'exchange' => self::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_DLX_EXCHAGE,
                'type'=> 'fanout',
            ],

            // EMISSAO BOLETO
            [
                'exchange' => self::EMISSAO_BOLETO_EXCHANGE,
                'type'=> 'direct',
            ],
            [
                'exchange' => self::EMISSAO_BOLETO_DLX_EXCHANGE,
                'type'=> 'fanout',
            ],

            
            // EMAIL
            [
                'exchange' => self::EMISSAO_EMAIL_EXCHANGE,
                'type'=> 'direct',
            ],
            [
                'exchange' => self::EMISSAO_EMAIL_DLX_EXCHANGE,
                'type'=> 'fanout',
            ],


            // EMISSAO NFSE
            [
                'exchange' => self::EMISSAO_NFSE_EXCHANGE,
                'type'=> 'direct',
            ],
            [
                'exchange' => self::EMISSAO_NFSE_DLX_EXCHANGE,
                'type'=> 'fanout',
            ],

            // NOTIFICAR COMPROMISSOS
            [
                'exchange' => self::NOTIFICAR_COMPROMISSOS_EXCHANGE,
                'type'=> 'direct',
            ],
            [
                'exchange' => self::NOTIFICAR_COMPROMISSOS_DLX_EXCHANGE,
                'type'=> 'fanout',
            ]
        ];
    }
}