<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueRedis;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\Queue;
use Redis;
use RuntimeException;

final class RedisQueueExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

    public function register(Application $application): void
    {
        $application->container()->bind(
            Queue::class,
            fn (): RedisQueue => new RedisQueue(
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
            throw new RuntimeException('Nao foi possivel conectar ao Redis de fila.');
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
