<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class XdebugTest extends TestCase
{
    public function testXdebugStartWithRequestIsDisabled(): void
    {
        if (!extension_loaded('xdebug')) {
            $this->markTestSkipped('Xdebug not loaded.');
        }

        $value = ini_get('xdebug.start_with_request');

        $this->assertNotSame('1', $value, 'xdebug.start_with_request should be disabled to avoid blocking requests.');
        $this->assertNotSame('yes', strtolower((string) $value), 'xdebug.start_with_request should be disabled to avoid blocking requests.');
    }
}
