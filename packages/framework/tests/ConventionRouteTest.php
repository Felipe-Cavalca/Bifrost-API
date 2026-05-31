<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures/App/Http/Controller/HealthController.php';

final class ConventionRouteTest extends TestCase
{
    public function testDispatchesControllerActionByConvention(): void
    {
        $application = Application::create();

        $response = $application->handle(new Request(method: 'GET', path: '/health/show'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"action":"show"}', $response->body());
    }

    public function testUsesIndexAsDefaultConventionAction(): void
    {
        $application = Application::create();

        $response = $application->handle(new Request(method: 'GET', path: '/health'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"action":"index"}', $response->body());
    }

    public function testRegisteredRouteHasPriorityOverConvention(): void
    {
        $application = Application::create();
        $application->get('/health/show', fn (): Response => Response::json(['action' => 'registered']));

        $response = $application->handle(new Request(method: 'GET', path: '/health/show'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"action":"registered"}', $response->body());
    }

    public function testDoesNotExposePrivateControllerMethodByConvention(): void
    {
        $application = Application::create();

        $response = $application->handle(new Request(method: 'GET', path: '/health/internal'));

        self::assertSame(404, $response->status());
    }

    public function testDoesNotResolveConventionPathWithAdditionalSegments(): void
    {
        $application = Application::create();

        $response = $application->handle(new Request(method: 'GET', path: '/health/show/extra'));

        self::assertSame(404, $response->status());
    }
}
