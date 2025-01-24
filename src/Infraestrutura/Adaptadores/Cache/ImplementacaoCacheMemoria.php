<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Cache;

use App\Aplicacao\Compartilhado\Cache\Cache;

class ImplementacaoCacheMemoria implements Cache
{

    private array $cache = [];

    public function set(string $key, string $value, int $expireInSeconds = -1): void
    {
        $this->cache[$key] = [
            'value' => $value,
            'expire' => $expireInSeconds
        ];
    }

    public function get(string $key): string
    {
        return $this->cache[$key];
    }

    public function exist(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function ttl(string $key): int
    {
        return $this->cache[$key]['expire'];
    }

    public function delete(string $key = '', string $pattern = ''): void
    {
        if($key !== ''){
            unset($this->cache[$key]);
        }
    }
    public function expire(string $key, int $seconds): void
    {
        $this->cache[$key]['expire'] = $seconds;
    }
}