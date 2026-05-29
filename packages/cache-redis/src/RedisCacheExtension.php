<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheRedis;

use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Extension\Redis\RedisServiceRegistrar;
use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\CacheStore;
use Bifrost\Framework\Contracts\Extension;

final class RedisCacheExtension implements Extension
{
    private readonly RedisConfig $redisConfig;
    private readonly string $prefix;

    public function __construct(private readonly array $config)
    {
        $this->redisConfig = RedisConfig::fromArray($config);
        $this->prefix = (string) ($config['prefix'] ?? '');
    }

    public function register(Application $application): void
    {
        RedisServiceRegistrar::register($application);

        $application->container()->bind(
            CacheStore::class,
            fn (Container $container): RedisCache => new RedisCache(
                redis: $container->get(RedisConnectionFactory::class)->connect($this->redisConfig),
                prefix: $this->prefix
            )
        );
    }
}
