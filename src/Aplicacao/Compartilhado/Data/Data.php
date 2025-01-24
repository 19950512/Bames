<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Data;

interface Data
{
    public static function timeAgo(string $data): string;

    public static function relative(string $timeStamp): string;

    public static function mesCompleto(int $mes): string;
    public static function mesAbreviado(int $mes): string;
    public static function diaSemanaCompleto(int $dia): string;
    public static function diaSemanaAbreviado(int $dia): string;
    public static function diaDoMes(): int;

    public static function diaDoAno(): int;

    public static function semanaDoAno(): int;

    public static function ano(): int;

    public static function hora(): int;

    public static function minuto(): int;

    /**
    * @return d/m/Y H:i:s
     */
    public static function agora(): string;

    public static function dataHora(): string;
}
