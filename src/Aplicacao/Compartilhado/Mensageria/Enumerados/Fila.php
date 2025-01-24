<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Mensageria\Enumerados;

enum Fila: string
{
    case EMISSAO_EMAIL_QUEUE = 'emissao_email_queue';
    case EMISSAO_EMAIL_QUEUE_DLQ_QUEUE = 'emissao_email_queue_dlq_queue';

    case EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE = 'empresa_recem_cadastrada_no_sistema_queue';
    case EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE_DLQ_QUEU = 'empresa_recem_cadastrada_no_sistema_queue_dlq_queue';

    case NOTIFICAR_COMPROMISSOS_QUEUE = 'notificar_compromissos_queue';
    case NOTIFICAR_COMPROMISSOS_QUEUE_DLQ_QUEUE = 'notificar_compromissos_queue_dlq_queue';

    case NOVO_EVENTO_AGENDA_QUEUE = 'novo_evento_agenda_queue';
    case NOVO_EVENTO_AGENDA_QUEUE_DLQ_QUEUE = 'novo_evento_agenda_queue_dlq_queue';

    static public function Ligacoes(): array
    {
        return [

            // EVENTO AGENDA
            [
                'queue' => self::NOVO_EVENTO_AGENDA_QUEUE,
                'exchange' => TrocaMensagens::NOVO_EVENTO_AGENDA_EXCHANGE,
            ],
            [
                'queue' => self::NOVO_EVENTO_AGENDA_QUEUE_DLQ_QUEUE,
                'exchange' => TrocaMensagens::NOVO_EVENTO_AGENDA_DLX_EXCHANGE,
            ],

            // EMAIL
            [
                'queue' => self::EMISSAO_EMAIL_QUEUE,
                'exchange' => TrocaMensagens::EMISSAO_EMAIL_EXCHANGE,
            ],
            [
                'queue' => self::EMISSAO_EMAIL_QUEUE_DLQ_QUEUE,
                'exchange' => TrocaMensagens::EMISSAO_EMAIL_DLX_EXCHANGE,
            ],

            // EMPRESA RECEM CADASTRADA
            [
                'queue' => self::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE,
                'exchange' => TrocaMensagens::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_EXCHAGE,
            ],
            [
                'queue' => self::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE_DLQ_QUEU,
                'exchange' => TrocaMensagens::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_DLX_EXCHAGE,
            ],

            // NOTIFICAR COMPROMISSOS
            [
                'queue' => self::NOTIFICAR_COMPROMISSOS_QUEUE,
                'exchange' => TrocaMensagens::NOTIFICAR_COMPROMISSOS_EXCHANGE,
            ],
            [
                'queue' => self::NOTIFICAR_COMPROMISSOS_QUEUE_DLQ_QUEUE,
                'exchange' => TrocaMensagens::NOTIFICAR_COMPROMISSOS_DLX_EXCHANGE,
            ]
        ];
    }

    static public function Filas(): array
    {
        return [

            // EVENTO AGENDA
            [
                'queue' => self::NOVO_EVENTO_AGENDA_QUEUE,
                'dlx' => TrocaMensagens::NOVO_EVENTO_AGENDA_DLX_EXCHANGE,
            ],
            [
                'queue' => self::NOVO_EVENTO_AGENDA_QUEUE_DLQ_QUEUE,
                'dlx' => TrocaMensagens::NOVO_EVENTO_AGENDA_DLX_EXCHANGE,
            ],

            // EMAIL
            [
                'queue' => Fila::EMISSAO_EMAIL_QUEUE,
                'dlx' => TrocaMensagens::EMISSAO_EMAIL_DLX_EXCHANGE,
            ],
            [
                'queue' => Fila::EMISSAO_EMAIL_QUEUE_DLQ_QUEUE,
                'dlx' => TrocaMensagens::EMISSAO_EMAIL_DLX_EXCHANGE,
            ],

            // EMPRESA RECEM CADASTRADA
            [
                'queue' => Fila::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE,
                'dlx' => TrocaMensagens::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_DLX_EXCHAGE
            ],
            [
                'queue' => Fila::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE_DLQ_QUEU,
                'dlx' => TrocaMensagens::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_DLX_EXCHAGE
            ],

            // NOTIFICAR COMPROMISSOS
            [
                'queue' => Fila::NOTIFICAR_COMPROMISSOS_QUEUE,
                'dlx' => TrocaMensagens::NOTIFICAR_COMPROMISSOS_DLX_EXCHANGE
            ],
            [
                'queue' => Fila::NOTIFICAR_COMPROMISSOS_QUEUE_DLQ_QUEUE,
                'dlx' => TrocaMensagens::NOTIFICAR_COMPROMISSOS_DLX_EXCHANGE
            ]
        ];
    }
}