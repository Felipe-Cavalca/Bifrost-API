<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Attributes\Method;
use Bifrost\Framework\Attributes\RequiredFields;
use Bifrost\Framework\Attributes\RequiredParams;
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

    public function testValidatesControllerAttributesBeforeAction(): void
    {
        $application = Application::create();
        $application->post('/users', [AttributeControllerStub::class, 'store']);

        $response = $application->handle(new Request(
            method: 'POST',
            path: '/users',
            query: ['page' => '1'],
            body: ['name' => 'Bifrost']
        ));

        self::assertSame(400, $response->status());
        self::assertStringContainsString('email', $response->body());
    }

    public function testAllowsRequestWhenControllerAttributesPass(): void
    {
        $application = Application::create();
        $application->post('/users', [AttributeControllerStub::class, 'store']);

        $response = $application->handle(new Request(
            method: 'POST',
            path: '/users',
            query: ['page' => '1'],
            body: ['name' => 'Bifrost', 'email' => 'team@bifrost.dev']
        ));

        self::assertSame(201, $response->status());
        self::assertJsonStringEqualsJsonString('{"created":true}', $response->body());
    }

    public function testReturnsControllerAttributeMetadataOnOptionsRequest(): void
    {
        $application = Application::create();
        $application->post('/users', [AttributeControllerStub::class, 'store']);

        $response = $application->handle(new Request(method: 'OPTIONS', path: '/users'));

        self::assertSame(200, $response->status());
        self::assertStringContainsString('attributes', $response->body());
        self::assertStringContainsString('email', $response->body());
    }
}

final class AttributeControllerStub
{
    #[Method('POST')]
    #[RequiredParams(['page' => 'int-string'])]
    #[RequiredFields(['name' => 'string', 'email' => 'email'])]
    public function store(Request $request): Response
    {
        return Response::json(['created' => true], status: 201);
    }
}
