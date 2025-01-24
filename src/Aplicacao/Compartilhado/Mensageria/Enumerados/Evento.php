<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Mensageria\Enumerados;

enum Evento: string
{
    case EnviarEmail = 'Enviar e-mail';

    case EmpresaRecemCadastradaNoSistema = 'Uma nova empresa acaba de ser cadastrada no sistema';

    case NotificarCompromissos = 'Notificar compromissos';

    case NovoEventoAgenda = 'Novo evento na agenda';

    public function Filas(): Fila
    {
        return match ($this) {
            self::EnviarEmail => Fila::EMISSAO_EMAIL_QUEUE,
            self::EmpresaRecemCadastradaNoSistema => Fila::EMPRESA_RECEM_CADASTRADA_NO_SISTEMA_QUEUE,
            self::NotificarCompromissos => Fila::NOTIFICAR_COMPROMISSOS_QUEUE,
            self::NovoEventoAgenda => Fila::NOVO_EVENTO_AGENDA_QUEUE,
        };
    }
}