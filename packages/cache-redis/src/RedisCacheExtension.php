<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheRedis;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\CacheStore;
use Bifrost\Framework\Contracts\Extension;
use Redis;
use RuntimeException;

final class RedisCacheExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

    public function register(Application $application): void
    {
        $application->container()->bind(
            CacheStore::class,
            fn (): RedisCache => new RedisCache(
                redis: $this->connect(),
                prefix: (string) ($this->config['prefix'] ?? '')
            )
        );
    }

    private function connect(): Redis
    {
        $redis = new Redis();
        $connected = $redis->connect(
            (string) ($this->config['host'] ?? '127.0.0.1'),
            (int) ($this->config['port'] ?? 6379),
            (float) ($this->config['timeout'] ?? 0.0)
        );

        if (!$connected) {
            throw new RuntimeException('Nao foi possivel conectar ao Redis de cache.');
        }

        if (isset($this->config['password'])) {
            $redis->auth((string) $this->config['password']);
        }

        if (isset($this->config['database'])) {
            $redis->select((int) $this->config['database']);
        }

        return $redis;
    }
}
