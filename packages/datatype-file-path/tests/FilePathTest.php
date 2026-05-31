<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\FilePath\Tests;

use Bifrost\DataTypes\Filesystem\FilePath;
use PHPUnit\Framework\TestCase;

final class FilePathTest extends TestCase
{
    public function testValidatesFilePath(): void
    {
        self::assertTrue(FilePath::isValid('avatars/user.png'));
        self::assertFalse(FilePath::isValid('../secret.txt'));
        self::assertFalse(FilePath::isValid('/secret.txt'));
    }
}
