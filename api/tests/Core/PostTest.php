<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    public function testProvidesAccessToDecodedInputData(): void
    {
        $post = bifrost_set_post_data(['name' => 'Alice', 'age' => 30]);

        self::assertSame('Alice', $post->name);
        self::assertTrue(isset($post->age));
        self::assertSame('{"name":"Alice","age":30}', (string) $post);

        unset($post->age);

        self::assertFalse(isset($post->age));
    }
}
