<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Base64\Tests;

use Bifrost\DataTypes\Base64;
use PHPUnit\Framework\TestCase;

final class Base64Test extends TestCase
{
    public function testValidatesBase64(): void
    {
        self::assertTrue(Base64::isValid(base64_encode('bifrost')));
        self::assertFalse(Base64::isValid('###'));
    }
}
