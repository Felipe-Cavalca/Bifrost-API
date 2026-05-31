<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Json\Tests;

use Bifrost\DataTypes\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{
    public function testValidatesJson(): void
    {
        self::assertTrue(Json::isValid('{"name":"bifrost"}'));
        self::assertFalse(Json::isValid('{invalid'));
    }
}
