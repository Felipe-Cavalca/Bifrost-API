<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

interface Queue
{
    public function push(string $queue, array $payload): void;

    public function pop(string $queue): ?array;
}
