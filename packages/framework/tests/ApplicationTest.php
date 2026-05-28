<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Attributes\Method;
use Bifrost\Framework\Attributes\RequiredFields;
use Bifrost\Framework\Attributes\RequiredParams;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Exceptions\HttpException;
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

    public function testPreservesIncomingRequestIdInResponseHeader(): void
    {
        $application = Application::create();
        $application->get('/health', fn (): Response => Response::json(['status' => 'healthy']));

        $response = $application->handle(new Request(
            method: 'GET',
            path: '/health',
            headers: ['X-Request-Id' => 'req-123']
        ));

        self::assertSame('req-123', $response->headers()['X-Request-Id']);
    }

    public function testGeneratesRequestIdWhenHeaderIsMissing(): void
    {
        $application = Application::create();
        $application->get('/health', fn (): Response => Response::json(['status' => 'healthy']));

        $response = $application->handle(new Request(method: 'GET', path: '/health'));

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $response->headers()['X-Request-Id']);
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
        self::assertJsonStringEqualsJsonString(
            sprintf(
                '{"message":"Internal Server Error","request_id":"%s"}',
                $response->headers()['X-Request-Id']
            ),
            $response->body()
        );
    }

    public function testAddsRequestIdToNotFoundErrorPayload(): void
    {
        $application = Application::create();

        $response = $application->handle(new Request(
            method: 'GET',
            path: '/missing',
            headers: ['X-Request-Id' => 'req-not-found']
        ));

        self::assertSame(404, $response->status());
        self::assertJsonStringEqualsJsonString(
            '{"message":"Not Found","request_id":"req-not-found"}',
            $response->body()
        );
    }

    public function testConvertsHttpExceptionToJsonResponse(): void
    {
        $application = Application::create();
        $application->post('/users', static function (): Response {
            throw HttpException::badRequest('Invalid payload', ['fields' => ['email' => 'Invalid field type']]);
        });

        $response = $application->handle(new Request(
            method: 'POST',
            path: '/users',
            headers: ['X-Request-Id' => 'req-http-error']
        ));

        self::assertSame(400, $response->status());
        self::assertSame('req-http-error', $response->headers()['X-Request-Id']);
        self::assertJsonStringEqualsJsonString(
            '{"message":"Invalid payload","errors":{"fields":{"email":"Invalid field type"}},"request_id":"req-http-error"}',
            $response->body()
        );
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
