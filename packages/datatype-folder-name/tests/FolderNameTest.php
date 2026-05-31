<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\FolderName\Tests;

use Bifrost\DataTypes\Filesystem\FolderName;
use PHPUnit\Framework\TestCase;

final class FolderNameTest extends TestCase
{
    public function testValidatesFolderName(): void
    {
        self::assertTrue(FolderName::isValid('avatars'));
        self::assertFalse(FolderName::isValid('some.folder'));
    }
}
