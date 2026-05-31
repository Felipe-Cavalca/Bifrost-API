<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    public function testDispatchesRegisteredRoute(): void
    {
        $application = Application::create();
        $application->get('/health', fn (): Response => Response::json(['status' => 'healthy']));

        $response = $application->handle(new Request(method: 'GET', path: '/health'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"status":"healthy"}', $response->body());
    }

    public function testReportsMethodNotAllowed(): void
    {
        $application = Application::create();
        $application->get('/health', fn (): Response => Response::json(['status' => 'healthy']));

        $response = $application->handle(new Request(method: 'POST', path: '/health'));

        self::assertSame(405, $response->status());
        self::assertSame('GET', $response->headers()['Allow']);
    }

    public function testExecutesMiddlewareAroundRoute(): void
    {
        $application = Application::create();
        $application->middleware(
            fn (Request $request, callable $next): Response => $next($request)->withHeader('X-Framework', 'Bifrost')
        );
        $application->get('/', fn (): string => 'ok');

        $response = $application->handle(new Request(method: 'GET', path: '/'));

        self::assertSame('Bifrost', $response->headers()['X-Framework']);
        self::assertSame('ok', $response->body());
    }

    public function testRegistersExtensionInContainer(): void
    {
        $application = Application::create();
        $application->extend(new class implements Extension {
            public function register(Application $application): void
            {
                $application->container()->instance('clock', new \stdClass());
            }
        });

        self::assertTrue($application->container()->has('clock'));
    }

    public function testDoesNotExposeUnexpectedExceptionByDefault(): void
    {
        $application = Application::create();
        $application->get('/failure', static function (): Response {
            throw new \RuntimeException('database-password=secret');
        });

        $response = $application->handle(new Request(method: 'GET', path: '/failure'));

        self::assertSame(500, $response->status());
        self::assertStringNotContainsString('secret', $response->body());
        self::assertJsonStringEqualsJsonString('{"message":"Internal Server Error"}', $response->body());
    }
}
