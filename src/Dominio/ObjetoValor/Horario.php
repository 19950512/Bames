<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

readonly final class Horario
{
    public Turno $turno;
    public function __construct(
        private int $hora,
        private int $minuto,
        private int $segundo = 0
    ){
        if($hora < 0 || $hora > 23){
            throw new Exception('Hora inválida');
        }

        if($minuto < 0 || $minuto > 59){
            throw new Exception('Minuto inválido');
        }

        if($segundo < 0 || $segundo > 59){
            throw new Exception('Segundo inválido');
        }

        $this->turno = match(true){
            $hora >= 6 && $hora < 12 => Turno::MANHA,
            $hora >= 12 && $hora < 18 => Turno::TARDE,
            default => Turno::NOITE
        };
    }

    public static function criar(string $horario): Horario
    {
        $horario = explode(':', $horario);
        return new Horario(
            hora: (int) ($horario[0] ?? 0),
            minuto: (int) ($horario[1] ?? 0),
            segundo: (int) ($horario[2] ?? 0)
        );
    }

    public function get(): string
    {
        $hora = str_pad((string) $this->hora, 2, '0', STR_PAD_LEFT);
        $minuto = str_pad((string) $this->minuto, 2, '0', STR_PAD_LEFT);
        $segundo = str_pad((string) $this->segundo, 2, '0', STR_PAD_LEFT);
        
        return "{$hora}:{$minuto}:{$segundo}";
    }

    public function diferenca(Horario $horario): int
    {
        $segundos = ($this->hora * 3600 + $this->minuto * 60 + $this->segundo) - ($horario->hora * 3600 + $horario->minuto * 60 + $horario->segundo);
        return abs($segundos);
    }

    public function getHora(): int
    {
        return $this->hora;
    }

    public function getMinuto(): int
    {
        return $this->minuto;
    }

    public function getSegundo(): int
    {
        return $this->segundo;
    }
}

enum Turno: string
{
    case MANHA = 'manha';
    case TARDE = 'tarde';
    case NOITE = 'noite';
}