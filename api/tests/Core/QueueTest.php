<?php

declare(strict_types=1);

use Bifrost\Core\Queue;
use Bifrost\Interface\Task;
use PHPUnit\Framework\TestCase;

final class QueueTest extends TestCase
{
    public function testFallsBackToImmediateExecutionWhenRedisIsDisabled(): void
    {
        $task = new class implements Task {
            public int $runs = 0;

            public function __serialize(): array
            {
                return [];
            }

            public function __unserialize(array $data): void
            {
            }

            public function run(): bool
            {
                $this->runs++;
                return true;
            }
        };

        $queue = new Queue();
        $queue->addToFront($task);
        $queue->addToEnd($task);
        $queue->addScheduledTask($task, 5);

        self::assertSame(3, $task->runs);
        self::assertNull($queue->getNextTask());
    }
}
