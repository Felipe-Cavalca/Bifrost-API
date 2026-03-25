<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AutoloadTest extends TestCase
{
    public function testCoreClassesCanBeAutoloaded(): void
    {
        self::assertTrue(class_exists(Bifrost\Core\Request::class));
        self::assertTrue(class_exists(Bifrost\Controller\Index::class));
        self::assertTrue(class_exists(Bifrost\DataTypes\Base64::class));
    }
}
