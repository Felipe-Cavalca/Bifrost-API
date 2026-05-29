<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\FolderPath\Tests;

use Bifrost\DataTypes\Filesystem\FolderPath;
use PHPUnit\Framework\TestCase;

final class FolderPathTest extends TestCase
{
    public function testValidatesFolderPath(): void
    {
        self::assertTrue(FolderPath::isValid('/var/uploads'));
        self::assertFalse(FolderPath::isValid('/var//uploads'));
    }
}
