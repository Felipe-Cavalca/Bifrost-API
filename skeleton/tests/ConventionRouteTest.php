<?php

declare(strict_types=1);

use Bifrost\Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class ConventionRouteTest extends TestCase
{
    public function testControllerActionRespondsWithoutManualRoute(): void
    {
        $application = require dirname(__DIR__) . '/core/bootstrap/app.php';

        $response = $application->handle(new Request(method: 'GET', path: '/health/show'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"status":"ok"}', $response->body());
    }
}
