<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Ambiente;

interface Ambiente {
    public static function get(string $key): string | bool | int;
}