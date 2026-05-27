<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueRedis;

use Bifrost\Framework\Contracts\Queue;
use Redis;
use UnexpectedValueException;

final class RedisQueue implements Queue
{
    public function __construct(
        private readonly Redis $redis,
        private readonly string $prefix = ''
    ) {
    }

    public function push(string $queue, array $payload): void
    {
        $this->redis->rPush($this->queueKey($queue), serialize($payload));
    }

    public function pop(string $queue): ?array
    {
        $payload = $this->redis->lPop($this->queueKey($queue));

        if ($payload === false) {
            return null;
        }

        $value = unserialize($payload, ['allowed_classes' => false]);

        if (!is_array($value)) {
            throw new UnexpectedValueException('Payload invalido recebido da fila Redis.');
        }

        return $value;
    }

    private function queueKey(string $queue): string
    {
        return $this->prefix . $queue;
    }
}
