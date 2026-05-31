<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueRedis;

use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Extension\Redis\RedisServiceRegistrar;
use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\Queue;

final class RedisQueueExtension implements Extension
{
    private readonly RedisConfig $redisConfig;
    private readonly string $prefix;

    /**
     * @param array{host?: string, port?: int|string, timeout?: float|int|string, password?: string|null, database?: int|string|null, prefix?: string} $config
     */
    public function __construct(private readonly array $config)
    {
        $this->redisConfig = RedisConfig::fromArray($config);
        $this->prefix = (string) ($config['prefix'] ?? '');
    }

    /**
     * Registra a fila Redis usando a factory Redis compartilhada.
     */
    public function register(Application $application): void
    {
        RedisServiceRegistrar::register($application);

        $application->container()->bind(
            Queue::class,
            fn (Container $container): RedisQueue => new RedisQueue(
                redis: $container->get(RedisConnectionFactory::class)->connect($this->redisConfig),
                prefix: $this->prefix
            )
        );
    }
}
