<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Contrato\Enumerados;

enum Status: string
{
    case RASCUNHO =             'RASCUNHO';                 // Quando o contrato está sendo criado, ainda não está pronto.
    case EM_CONFIGURACAO =      'EM_CONFIGURACAO';          // Quando o contrato está sendo configurado, mas não finalizado.
    case AGUARDANDO_APROVACAO = 'AGUARDANDO_APROVACAO';     // Quando o contrato aguarda aprovação ou revisão.
    case EM_VIGOR =             'EM_VIGOR';                 // Quando o contrato foi efetivado e está em vigor.
    case EM_EXECUCAO =          'EM_EXECUCAO';              // Quando as obrigações do contrato estão sendo cumpridas.
    case CONCLUIDO =            'CONCLUIDO';                // Quando todas as obrigações foram cumpridas e o contrato foi finalizado.
    case ENCERRADO =            'ENCERRADO';                // Quando o contrato foi finalizado antecipadamente (rescisão ou acordo).
    case CANCELADO =            'CANCELADO';                // Quando o contrato foi cancelado antes do cumprimento total.
}