<?php

declare(strict_types=1);

use Bifrost\Core\Get;
use PHPUnit\Framework\TestCase;

final class GetRouteMappedTest extends TestCase
{
    public function testMarksMappedRoute(): void
    {
        bifrost_reset_get(['_controller' => 'ping']);

        self::assertTrue(Get::$routeMapped);
    }

    public function testMarksUnmappedRoute(): void
    {
        bifrost_reset_get(['_controller' => 'index', '_action' => 'health']);

        self::assertFalse(Get::$routeMapped);
    }
}
