<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AutoloadReturnTest extends TestCase
{
    public function testBifrostAutoloaderReturnsFalseWhenClassIsOutsidePrefix(): void
    {
        self::assertFalse($this->bifrostAutoloader()('Vendor\\MissingClass'));
    }

    public function testBifrostAutoloaderReturnsFalseWhenFileDoesNotExist(): void
    {
        self::assertFalse($this->bifrostAutoloader()('Bifrost\\MissingClass'));
    }

    private function bifrostAutoloader(): Closure
    {
        foreach (spl_autoload_functions() as $autoloader) {
            if (!$autoloader instanceof Closure) {
                continue;
            }

            $reflection = new ReflectionFunction($autoloader);
            if ($reflection->getFileName() === dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Autoload.php') {
                return $autoloader;
            }
        }

        self::fail('Bifrost autoloader not found.');
    }
}
