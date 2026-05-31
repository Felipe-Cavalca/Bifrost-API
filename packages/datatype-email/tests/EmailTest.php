<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Email\Tests;

use Bifrost\DataTypes\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidatesAndNormalizesEmail(): void
    {
        self::assertSame('team@bifrost.dev', Email::from('TEAM@BIFROST.DEV')->value());
        self::assertFalse(Email::isValid('invalid'));
    }
}
