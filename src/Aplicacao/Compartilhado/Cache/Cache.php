<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Cache;

interface Cache
{
    public function exist(string $key): bool;
    public function get(string $key): string;
    public function ttl(string $key): int;
    public function set(string $key, string $value, int $expireInSeconds): void;
    public function delete(string $key = '', string $pattern = ''): void;
    public function expire(string $key, int $seconds): void;
}

