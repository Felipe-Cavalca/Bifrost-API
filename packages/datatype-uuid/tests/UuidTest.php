<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Uuid\Tests;

use Bifrost\DataTypes\Uuid;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function testValidatesAndGeneratesUuid(): void
    {
        self::assertTrue(Uuid::isValid(Uuid::generate()->value()));
        self::assertFalse(Uuid::isValid('invalid'));
    }
}
