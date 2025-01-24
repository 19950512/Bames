<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Data;

abstract class DataDefault
{
    public static $mesesCompleto = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro'
        ];

    public static $mesesAbreviado = [
        1 => 'Jan',
        2 => 'Fev',
        3 => 'Mar',
        4 => 'Abr',
        5 => 'Mai',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Ago',
        9 => 'Set',
        10 => 'Out',
        11 => 'Nov',
        12 => 'Dez'
    ];

    public static $diaSemanaCompleto = [
        0 => 'Domingo',
        1 => 'Segunda-Feira',
        2 => 'Terça-Feira',
        3 => 'Quarta-Feira',
        4 => 'Quinta-Feira',
        5 => 'Sexta-Feira',
        6 => 'Sábado'
    ];

    public static $diaSemanaAbreviado = [
        0 => 'Dom',
        1 => 'Seg',
        2 => 'Ter',
        3 => 'Qua',
        4 => 'Qui',
        5 => 'Sex',
        6 => 'Sáb',
    ];

    public static function _mesCompleto(int $mes): string
    {
        return self::$mesesCompleto[$mes];
    }

    public static function _mesAbreviado(int $mes): string
    {
        return self::$mesesAbreviado[$mes];
    }

    public static function _diaSemanaCompleto(int $dia): string
    {
        return self::$diaSemanaCompleto[$dia];
    }

    public static function _diaSemanaAbreviado(int $dia): string
    {
        return self::$diaSemanaAbreviado[$dia];
    }

    public static function _diaDoMes(): int
    {
        return (int) date('d');
    }

    public static function _diaDoAno(): int
    {
        return (int) date('z');
    }

    public static function _semanaDoAno(): int
    {
        return (int) date('W');
    }

    public static function _ano(): int
    {
        return (int) date('Y');
    }

    public static function _hora(): int
    {
        return (int) date('H');
    }

    public static function _minuto(): int
    {
        return (int) date('i');
    }

    public static function _agora(): string
    {
        return date('d/m/Y H:i:s');
    }

    public static function _dataHora(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function _data(): string
    {
        return date('Y-m-d');
    }

    public static function _horaMinuto(): string
    {
        return date('H:i');
    }

    public static function _dataHoraFormatada(string $dataHora): string
    {
        return date('d/m/Y H:i:s', strtotime($dataHora));
    }
}