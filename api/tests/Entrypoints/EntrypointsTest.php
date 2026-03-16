<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EntrypointsTest extends TestCase
{
    public function testEntrypointFilesExist(): void
    {
        self::assertFileExists(__DIR__ . '/../../index.php');
        self::assertFileExists(__DIR__ . '/../../Worker.php');
        self::assertFileExists(__DIR__ . '/../../Controller/index.php');
    }

    public function testEntrypointsReferenceExpectedBootstrapClasses(): void
    {
        self::assertStringContainsString('new Request()', file_get_contents(__DIR__ . '/../../index.php'));
        self::assertStringContainsString('new Queue()', file_get_contents(__DIR__ . '/../../Worker.php'));
        self::assertStringContainsString('HttpResponse::success', file_get_contents(__DIR__ . '/../../Controller/index.php'));
    }
}
