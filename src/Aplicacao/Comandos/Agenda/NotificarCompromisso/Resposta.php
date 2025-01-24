<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\NotificarCompromisso;

enum Resposta: string
{
    case JA_PASSOU = 'O evento já passou.';
    case NOTIFICADO = 'Evento notificado.';
    case AINDA_NAO_E_HORA = 'Ainda não é hora de notificar o evento.';
    case NAO_ENCONTRADO = 'Evento não encontrado.';
}