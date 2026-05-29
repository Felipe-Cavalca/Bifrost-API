<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheRedis;

use Bifrost\Framework\Contracts\CacheStore;
use Redis;

final class RedisCache implements CacheStore
{
    /**
     * @param Redis $redis Conexao Redis reutilizavel.
     * @param string $prefix Prefixo aplicado nas chaves.
     */
    public function __construct(
        private readonly Redis $redis,
        private readonly string $prefix = ''
    ) {
    }

    /**
     * Recupera um valor serializado do Redis.
     */
    public function get(string $key): mixed
    {
        $value = $this->redis->get($this->cacheKey($key));

        if ($value === false) {
            return null;
        }

        return unserialize($value, ['allowed_classes' => true]);
    }

    /**
     * Armazena um valor serializado no Redis.
     */
    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $key = $this->cacheKey($key);
        $value = serialize($value);

        if ($ttlSeconds === null) {
            $this->redis->set($key, $value);
            return;
        }

        if ($ttlSeconds <= 0) {
            $this->redis->del($key);
            return;
        }

        $this->redis->setex($key, $ttlSeconds, $value);
    }

    /**
     * Remove uma chave do Redis.
     */
    public function delete(string $key): void
    {
        $this->redis->del($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
