<?php

declare(strict_types=1);

use Bifrost\Class\TextResponse;
use PHPUnit\Framework\TestCase;

final class TextResponseTest extends TestCase
{
    public function testTextResponseSerializesText(): void
    {
        self::assertSame('pong', (new TextResponse('pong'))->jsonSerialize());
    }
}
