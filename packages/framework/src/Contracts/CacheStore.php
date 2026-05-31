<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

interface CacheStore
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void;

    public function delete(string $key): void;
}
