<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\StorageKey\Tests;

use Bifrost\DataTypes\Storage\StorageKey;
use PHPUnit\Framework\TestCase;

final class StorageKeyTest extends TestCase
{
    public function testValidatesStorageKey(): void
    {
        self::assertTrue(StorageKey::isValid('documents/report.pdf'));
        self::assertSame('documents/report.pdf', StorageKey::from('documents\\report.pdf')->value());
        self::assertFalse(StorageKey::isValid('/documents/report.pdf'));
    }
}
