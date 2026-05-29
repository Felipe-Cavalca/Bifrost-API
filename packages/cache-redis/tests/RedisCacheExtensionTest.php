<?php

declare(strict_types=1);

use Bifrost\Extension\CacheRedis\RedisCache;
use Bifrost\Extension\CacheRedis\RedisCacheExtension;
use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\CacheStore;
use PHPUnit\Framework\TestCase;

final class RedisCacheExtensionTest extends TestCase
{
    public function testUsesSharedRedisConnectionFactory(): void
    {
        $factory = new CacheRedisCountingConnectionFactory();
        $application = Application::create();
        $application->container()->instance(RedisConnectionFactory::class, $factory);

        $application->extend(new RedisCacheExtension([
            'host' => 'redis',
            'port' => 6379,
            'database' => 0,
            'prefix' => 'cache:',
        ]));

        self::assertInstanceOf(RedisCache::class, $application->container()->get(CacheStore::class));
        self::assertSame(1, $factory->connections);
        self::assertSame('redis', $factory->lastConfig?->host);
        self::assertSame(0, $factory->lastConfig?->database);
    }
}

final class CacheRedisCountingConnectionFactory implements RedisConnectionFactory
{
    public int $connections = 0;
    public ?RedisConfig $lastConfig = null;

    public function connect(RedisConfig $config): Redis
    {
        $this->connections++;
        $this->lastConfig = $config;

        return new Redis();
    }
}
