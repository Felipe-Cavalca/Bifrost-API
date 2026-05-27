<?php

declare(strict_types=1);

use Bifrost\Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function testHealthEndpointRespondsWithoutOptionalExtensions(): void
    {
        $application = require dirname(__DIR__) . '/bootstrap/app.php';

        $response = $application->handle(new Request(method: 'GET', path: '/health'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"status":"ok"}', $response->body());
    }
}
