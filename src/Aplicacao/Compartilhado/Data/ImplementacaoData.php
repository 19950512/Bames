<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Data;

use Exception;

final class ImplementacaoData extends DataDefault implements Data
{
    public static function diaSemanaCompleto(int $dia): string
    {
        return self::$diaSemanaCompleto[$dia] ?? 'Inválido';
    }

    public static function diaSemanaAbreviado(int $dia): string
    {
        return self::$diaSemanaAbreviado[$dia] ?? 'Inválido';
    }

    public static function mesAbreviado(int $mes): string
    {
        return self::$mesesAbreviado[$mes] ?? 'Inválido';
    }


    public static function timeAgo(string $timeStamp): string
    {
        $currentTimestamp = time();

        $diff = $currentTimestamp - strtotime($timeStamp);

        $intervals = array(
            'ano' => 31536000,
            'mes' => 2592000,
            'semana' => 604800,
            'dia' => 86400,
            'hora' => 3600,
            'minuto' => 60,
            'segundo' => 1
        );

		$output = '';
        foreach ($intervals as $interval => $seconds) {
            $quantity = floor($diff / $seconds);
            if ($quantity > 0) {
                if ($quantity == 1) {
                    $output = "há $quantity $interval";
                } else {
					$plural = $quantity > 1 ? 's' : '';
					if($interval == 'mes' and $quantity > 1){
						$plural = 'es';
					}
                    $output = "há $quantity {$interval}{$plural}";
                }
                break;
            }
        }

		if(empty($output)){
			return self::relative($timeStamp);
		}

        return $output;
    }

    public static function relative(string $timeStamp): string
    {
        if(!ctype_digit($timeStamp))
            $timeStamp = strtotime($timeStamp);

        $diff = time() - $timeStamp;
        if($diff == 0)
            return 'agora';
        elseif($diff > 0)
        {
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 60) return 'agora';
                if($diff < 120) return 'há 1 minuto';
                if($diff < 3600) return 'há '.floor($diff / 60) . ' minutos';
                if($diff < 7200) return 'há 1 hora';
                if($diff < 86400) return 'há '.floor($diff / 3600) . ' horas';
            }
            if($day_diff == 1) return 'Hoje';
            if($day_diff < 7) return 'há '.$day_diff . ' dias';
            if($day_diff < 31){
				$week_diff = ceil($day_diff / 7);
				$semanaPlural = $week_diff > 1 ? 's' : '';
				return $week_diff <= 1 ? 'semana passada' : 'há '.$week_diff.' semana'.$semanaPlural;
            }
            if($day_diff < 60) return 'mês passado';
            return date('F Y', $timeStamp);
        }
        else
        {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 120) return 'em um minuto';
                if($diff < 3600) return 'em ' . floor($diff / 60) . ' minutos';
                if($diff < 7200) return 'em uma hora';
                if($diff < 86400) return 'em ' . floor($diff / 3600) . ' horas';
            }
            if($day_diff == 1) return 'amanhã';
            if($day_diff < 4) return date('l', $timeStamp);
            if($day_diff < 7 + (7 - date('w'))) return 'semana que vem';
            if(ceil($day_diff / 7) < 4) return 'em ' . ceil($day_diff / 7) . ' semanas';
            if(date('n', $timeStamp) == date('n') + 1) return 'mês que vem';
            return date('F Y', $timeStamp);
        }
    }

    public static function mesCompleto(int $mes): string
    {
        return self::$mesesCompleto[$mes] ?? 'Inválido';
    }

    public static function diaDoMes(): int
    {
        return self::_diaDoMes();
    }

    public static function diaDoAno(): int
    {
        return self::_diaDoAno();
    }

    public static function semanaDoAno(): int
    {
        return self::_semanaDoAno();
    }

    public static function ano(): int
    {
        return self::_ano();
    }

    public static function hora(): int
    {
        return self::_hora();
    }

    public static function minuto(): int
    {
        return self::_minuto();
    }

    /**
    ** @return 'd/m/Y H:i:s'
     */
    public static function agora(): string
    {
        return self::_agora();
    }

    public static function dataHora(): string
    {
        return self::_dataHora();
    }

    public static function dataHoraFormatada(string $formato): string
    {
        return self::_dataHoraFormatada($formato);
    }
}

