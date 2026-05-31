<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheApcu;

use Bifrost\Framework\Contracts\CacheStore;

final class ApcuCache implements CacheStore
{
    /**
     * @param string $prefix Prefixo aplicado nas chaves.
     * @param int|null $defaultTtlSeconds TTL padrao em segundos.
     */
    public function __construct(
        private readonly string $prefix = '',
        private readonly ?int $defaultTtlSeconds = null
    ) {
    }

    /**
     * Recupera um valor do cache APCu.
     */
    public function get(string $key): mixed
    {
        $success = false;
        $value = apcu_fetch($this->cacheKey($key), $success);

        return $success ? $value : null;
    }

    /**
     * Armazena um valor no cache APCu.
     */
    public function set(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= $this->defaultTtlSeconds;

        if ($ttlSeconds !== null && $ttlSeconds <= 0) {
            $this->delete($key);
            return;
        }

        apcu_store($this->cacheKey($key), $value, $ttlSeconds ?? 0);
    }

    /**
     * Remove uma chave do cache APCu.
     */
    public function delete(string $key): void
    {
        apcu_delete($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
