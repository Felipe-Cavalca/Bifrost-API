<?php

declare(strict_types=1);

use Bifrost\Extension\CacheApcu\ApcuCache;
use Bifrost\Extension\CacheApcu\ApcuCacheExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\CacheStore;
use PHPUnit\Framework\TestCase;

final class ApcuCacheTest extends TestCase
{
    protected function setUp(): void
    {
        if (!apcu_enabled()) {
            self::markTestSkipped('APCu precisa estar habilitado para testar o adapter.');
        }

        apcu_clear_cache();
    }

    public function testRegistersStoreAndDeletesCachedValue(): void
    {
        $application = Application::create()->extend(new ApcuCacheExtension([
            'prefix' => 'test:cache:',
        ]));
        $cache = $application->container()->get(CacheStore::class);

        self::assertInstanceOf(ApcuCache::class, $cache);

        $cache->set('sample', ['status' => 'ok'], 60);
        self::assertSame(['status' => 'ok'], $cache->get('sample'));

        $cache->delete('sample');
        self::assertNull($cache->get('sample'));
    }

    public function testKeepsValuesIsolatedByPrefix(): void
    {
        $first = new ApcuCache(prefix: 'first:', defaultTtlSeconds: null);
        $second = new ApcuCache(prefix: 'second:', defaultTtlSeconds: null);

        $first->set('key', 'first-value');
        $second->set('key', 'second-value');

        self::assertSame('first-value', $first->get('key'));
        self::assertSame('second-value', $second->get('key'));
    }

    public function testReturnsStoredFalseInsteadOfCacheMiss(): void
    {
        $cache = new ApcuCache(prefix: 'false:');

        $cache->set('key', false);

        self::assertFalse($cache->get('key'));
    }

    public function testDeletesValueWhenTtlIsNotPositive(): void
    {
        $cache = new ApcuCache(prefix: 'ttl:', defaultTtlSeconds: 60);

        $cache->set('key', 'stored');
        $cache->set('key', 'ignored', 0);

        self::assertNull($cache->get('key'));
    }
}
