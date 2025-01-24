<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca\Enumerados;

enum Parcela: int
{
    case UNICA = 1;
    case DUAS = 2;
    case TRES = 3;
    case QUATRO = 4;
    case CINCO = 5;
    case SEIS = 6;
    case SETE = 7;
    case OITO = 8;
    case NOVE = 9;
    case DEZ = 10;
    case ONZE = 11;
    case DOZE = 12;
    case TREZE = 13;
    case QUATORZE = 14;
    case QUINZE = 15;
    case DEZESSEIS = 16;
    case DEZESSETE = 17;
    case DEZOITO = 18;
    case DEZENOVE = 19;
    case VINTE = 20;
    case VINTE_E_UMA = 21;
}