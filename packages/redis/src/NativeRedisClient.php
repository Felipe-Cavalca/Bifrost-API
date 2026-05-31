<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisClient;
use Redis;

/**
 * Adapter do cliente nativo ext-redis para o contrato Bifrost.
 */
final class NativeRedisClient implements RedisClient
{
    public function __construct(private readonly Redis $redis)
    {
    }

    public function get(string $key): string|false
    {
        return $this->redis->get($key);
    }

    public function set(string $key, string $value): bool
    {
        return $this->redis->set($key, $value);
    }

    public function setex(string $key, int $ttlSeconds, string $value): bool
    {
        return $this->redis->setex($key, $ttlSeconds, $value);
    }

    public function del(string ...$keys): int|false
    {
        return $this->redis->del($keys);
    }

    public function rPush(string $key, string $value): int|false
    {
        return $this->redis->rPush($key, $value);
    }

    public function lPop(string $key): string|false
    {
        return $this->redis->lPop($key);
    }
}
