<?php

declare(strict_types=1);

use Bifrost\Core\Cache;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    public function testBuildKeyGeneratesMd5NamespacedKey(): void
    {
        $key = Cache::buildKey('ns', 'foo', 'bar');

        self::assertSame('ns:' . md5('foo|bar'), $key);
    }

    public function testBuildKeyAcceptsMixedParts(): void
    {
        $obj = new stdClass();
        $obj->a = 1;

        $key = Cache::buildKey('mixed', ['x'], $obj, 123, null);

        $expectedPayload = implode('|', [
            serialize(['x']),
            serialize($obj),
            '123',
            '',
        ]);

        self::assertSame('mixed:' . md5($expectedPayload), $key);
    }
}
