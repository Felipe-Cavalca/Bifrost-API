<?php

declare(strict_types=1);

use Bifrost\Attributes\Cache as CacheAttribute;
use Bifrost\Class\HttpResponse;
use Bifrost\Integration\Cache\RedisCache;
use PHPUnit\Framework\TestCase;

final class CacheAttributeHitTest extends TestCase
{
    private FakeRedisForCacheAttribute $redis;

    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        bifrost_reset_get();
        bifrost_set_post_data([]);
        bifrost_reset_session();

        $this->redis = new FakeRedisForCacheAttribute(HttpResponse::success('cached'));
        $this->setRedisCacheState(redis: $this->redis, enabled: true);
    }

    protected function tearDown(): void
    {
        $this->setRedisCacheState(redis: null, enabled: true);
    }

    public function testAfterDoesNotRewriteCacheWhenBeforeReturnedHit(): void
    {
        $attribute = new CacheAttribute(60);

        $cached = $attribute->before();
        $attribute->after(HttpResponse::success('fresh'));

        self::assertInstanceOf(HttpResponse::class, $cached);
        self::assertSame(0, $this->redis->setCount);
    }

    private function setRedisCacheState(?FakeRedisForCacheAttribute $redis, bool $enabled): void
    {
        $redisProperty = new ReflectionProperty(RedisCache::class, 'redis');
        $redisProperty->setAccessible(true);
        $redisProperty->setValue(null, $redis);

        $enabledProperty = new ReflectionProperty(RedisCache::class, 'enabled');
        $enabledProperty->setAccessible(true);
        $enabledProperty->setValue(null, $enabled);
    }
}

final class FakeRedisForCacheAttribute
{
    public int $setCount = 0;
    private mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function exists(string $key): int
    {
        return 1;
    }

    public function get(string $key): string
    {
        return serialize($this->value);
    }

    public function set(string $key, string $value, int $expire): bool
    {
        $this->setCount++;
        return true;
    }

    public function del(string $key): int
    {
        return 1;
    }
}
