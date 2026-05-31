<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheApcu;

use Bifrost\Framework\Contracts\CacheStore;

final class ApcuCache implements CacheStore
{
    public function __construct(
        private readonly string $prefix = '',
        private readonly ?int $defaultTtlSeconds = null
    ) {
    }

    public function get(string $key): mixed
    {
        $success = false;
        $value = apcu_fetch($this->cacheKey($key), $success);

        return $success ? $value : null;
    }

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= $this->defaultTtlSeconds;

        if ($ttlSeconds !== null && $ttlSeconds <= 0) {
            $this->delete($key);
            return;
        }

        apcu_store($this->cacheKey($key), $value, $ttlSeconds ?? 0);
    }

    public function delete(string $key): void
    {
        apcu_delete($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
