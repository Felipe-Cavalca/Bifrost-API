<?php

declare(strict_types=1);

use Bifrost\Core\Settings;
use Bifrost\Integration\Redis\RedisConfig;
use PHPUnit\Framework\TestCase;

final class RedisConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('BFR_API_CACHE_DRIVER');
        putenv('BFR_API_CACHE_REDIS_HOST');
        putenv('BFR_API_CACHE_REDIS_PORT');
        putenv('BFR_API_QUEUE_NAME');
        putenv('BFR_API_QUEUE_REDIS_HOST');
        putenv('BFR_API_QUEUE_REDIS_PORT');
    }

    public function testCacheConfigIsDisabledWhenDriverIsNone(): void
    {
        putenv('BFR_API_CACHE_DRIVER=none');
        putenv('BFR_API_CACHE_REDIS_HOST=redis');
        putenv('BFR_API_CACHE_REDIS_PORT=6379');

        $config = RedisConfig::forCache(new Settings());

        self::assertFalse($config->isEnabled());
        self::assertFalse($config->isRedisDriver());
    }

    public function testCacheConfigReadsRedisConnection(): void
    {
        putenv('BFR_API_CACHE_DRIVER=redis');
        putenv('BFR_API_CACHE_REDIS_HOST=redis');
        putenv('BFR_API_CACHE_REDIS_PORT=6379');

        $config = RedisConfig::forCache(new Settings());

        self::assertTrue($config->isEnabled());
        self::assertSame('redis', $config->host());
        self::assertSame(6379, $config->port());
    }

    public function testQueueConfigKeepsDefaultQueueName(): void
    {
        putenv('BFR_API_QUEUE_REDIS_HOST');
        putenv('BFR_API_QUEUE_REDIS_PORT');
        putenv('BFR_API_QUEUE_NAME');

        $config = RedisConfig::forQueue(new Settings());

        self::assertFalse($config->isEnabled());
        self::assertSame('bifrost_queue', $config->queueName());
    }

    public function testQueueConfigReadsConfiguredQueueName(): void
    {
        putenv('BFR_API_QUEUE_REDIS_HOST=redis');
        putenv('BFR_API_QUEUE_REDIS_PORT=6379');
        putenv('BFR_API_QUEUE_NAME=jobs');

        $config = RedisConfig::forQueue(new Settings());

        self::assertTrue($config->isEnabled());
        self::assertSame('jobs', $config->queueName());
    }
}
