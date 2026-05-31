<?php

declare(strict_types=1);

namespace Bifrost\Extension\CacheApcu;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\CacheStore;
use Bifrost\Framework\Contracts\Extension;

final class ApcuCacheExtension implements Extension
{
    /**
     * @param array{prefix?: string, ttl?: int|null} $config
     */
    public function __construct(private readonly array $config = [])
    {
    }

    /**
     * Registra o cache APCu como CacheStore.
     */
    public function register(Application $application): void
    {
        $application->container()->bind(
            CacheStore::class,
            fn (): ApcuCache => new ApcuCache(
                prefix: (string) ($this->config['prefix'] ?? ''),
                defaultTtlSeconds: array_key_exists('ttl', $this->config)
                    ? ($this->config['ttl'] === null ? null : (int) $this->config['ttl'])
                    : null
            )
        );
    }
}
