<?php

declare(strict_types=1);

use Bifrost\Extension\CacheRedis\RedisCacheExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\CacheStore;
use PHPUnit\Framework\TestCase;

final class RedisCacheTest extends TestCase
{
    public function testStoresAndDeletesCachedValue(): void
    {
        $application = Application::create()->extend(new RedisCacheExtension([
            'host' => getenv('REDIS_HOST') ?: 'redis',
            'port' => (int) (getenv('REDIS_PORT') ?: 6379),
            'prefix' => 'test:cache:',
        ]));
        $cache = $application->container()->get(CacheStore::class);

        $cache->set('sample', ['status' => 'ok'], 60);
        self::assertSame(['status' => 'ok'], $cache->get('sample'));

        $cache->delete('sample');
        self::assertNull($cache->get('sample'));
    }
}
