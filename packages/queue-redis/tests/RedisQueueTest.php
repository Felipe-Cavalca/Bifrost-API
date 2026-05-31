<?php

declare(strict_types=1);

use Bifrost\Extension\QueueRedis\RedisQueueExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Queue;
use PHPUnit\Framework\TestCase;

final class RedisQueueTest extends TestCase
{
    public function testPushesAndPopsPayload(): void
    {
        $application = Application::create()->extend(new RedisQueueExtension([
            'host' => getenv('REDIS_HOST') ?: 'redis',
            'port' => (int) (getenv('REDIS_PORT') ?: 6379),
            'prefix' => 'test:queue:',
        ]));
        $queue = $application->container()->get(Queue::class);

        $queue->push('jobs', ['job' => 'ping']);

        self::assertSame(['job' => 'ping'], $queue->pop('jobs'));
        self::assertNull($queue->pop('jobs'));
    }
}
