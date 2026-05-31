<?php

declare(strict_types=1);

use Bifrost\Extension\QueueRedis\RedisQueue;
use Bifrost\Extension\QueueRedis\RedisQueueExtension;
use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Extension\Redis\RedisConfig;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Queue;
use PHPUnit\Framework\TestCase;

final class RedisQueueExtensionTest extends TestCase
{
    public function testUsesSharedRedisConnectionFactory(): void
    {
        $factory = new QueueRedisCountingConnectionFactory();
        $application = Application::create();
        $application->container()->instance(RedisConnectionFactory::class, $factory);

        $application->extend(new RedisQueueExtension([
            'host' => 'redis',
            'port' => 6379,
            'database' => 0,
            'prefix' => 'queue:',
        ]));

        self::assertInstanceOf(RedisQueue::class, $application->container()->get(Queue::class));
        self::assertSame(1, $factory->connections);
        self::assertSame('redis', $factory->lastConfig?->host);
        self::assertSame(0, $factory->lastConfig?->database);
    }
}

final class QueueRedisCountingConnectionFactory implements RedisConnectionFactory
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
