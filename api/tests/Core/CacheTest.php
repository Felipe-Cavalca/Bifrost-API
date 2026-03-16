<?php

declare(strict_types=1);

use Bifrost\Core\Cache;
use Bifrost\DataTypes\UUID;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    public function testBuildCacheKeySerializesValues(): void
    {
        $key = Cache::buildCacheKey('users', [
            'id' => 10,
            'tags' => ['a', 'b'],
            'uuid' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
        ]);

        self::assertSame(
            'users:id:10:tags:["a","b"]:uuid:123e4567-e89b-12d3-a456-426614174000',
            $key
        );
    }
}
