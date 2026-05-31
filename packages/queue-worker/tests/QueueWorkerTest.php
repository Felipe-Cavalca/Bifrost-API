<?php

declare(strict_types=1);

use Bifrost\Extension\QueueWorker\QueueWorker;
use Bifrost\Extension\QueueWorker\TaskHandler;
use Bifrost\Extension\QueueWorker\TaskPayload;
use Bifrost\Extension\QueueWorker\TaskRegistry;
use Bifrost\Framework\Contracts\Queue;
use PHPUnit\Framework\TestCase;

final class QueueWorkerTest extends TestCase
{
    public function testProcessesSerializedTaskPayload(): void
    {
        $queue = new FakeQueue();
        $handledPayloads = [];
        $registry = (new TaskRegistry())->add('emails.send', static function (TaskPayload $payload) use (&$handledPayloads): void {
            $handledPayloads[] = $payload->data();
        });
        $worker = new QueueWorker($queue, $registry);

        $queue->push('default', (new TaskPayload('emails.send', ['email' => 'user@example.com']))->toArray());
        $result = $worker->workOnce('default');

        self::assertTrue($result->isProcessed());
        self::assertSame([['email' => 'user@example.com']], $handledPayloads);
        self::assertNull($queue->pop('default'));
    }

    public function testRetriesFailedTaskUntilMaxAttempts(): void
    {
        $queue = new FakeQueue();
        $registry = (new TaskRegistry())->add('always.fail', new FailingTaskHandler());
        $worker = new QueueWorker($queue, $registry);

        $queue->push('default', (new TaskPayload('always.fail', maxAttempts: 2))->toArray());

        $firstResult = $worker->workOnce('default');
        self::assertTrue($firstResult->isRetried());
        self::assertSame(1, $firstResult->payload()?->attempts());

        $secondResult = $worker->workOnce('default');
        self::assertTrue($secondResult->isFailed());
        self::assertSame(2, $secondResult->payload()?->attempts());
        self::assertNull($queue->pop('default'));
    }

    public function testReturnsIdleWhenQueueIsEmpty(): void
    {
        $worker = new QueueWorker(new FakeQueue(), new TaskRegistry());

        self::assertTrue($worker->workOnce('default')->isIdle());
    }
}

final class FakeQueue implements Queue
{
    /** @var array<string, list<array>> */
    private array $items = [];

    public function push(string $queue, array $payload): void
    {
        $this->items[$queue][] = $payload;
    }

    public function pop(string $queue): ?array
    {
        if (($this->items[$queue] ?? []) === []) {
            return null;
        }

        return array_shift($this->items[$queue]);
    }
}

final class FailingTaskHandler implements TaskHandler
{
    public function handle(TaskPayload $payload): void
    {
        throw new RuntimeException('Falha intencional para teste.');
    }
}
