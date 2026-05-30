<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Application;
use Bifrost\Framework\Attributes\Method;
use Bifrost\Framework\Http\HttpMethod;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class HttpMethodTest extends TestCase
{
    public function testRequestAcceptsHttpMethodEnum(): void
    {
        $request = new Request(method: HttpMethod::Post, path: '/users');

        self::assertSame('POST', $request->method());
        self::assertSame(HttpMethod::Post, $request->httpMethod());
    }

    public function testApplicationRoutesAcceptHttpMethodEnum(): void
    {
        $application = Application::create();
        $application->route(HttpMethod::Patch, '/users/1', fn (): Response => Response::json(['updated' => true]));

        $response = $application->handle(new Request(method: 'PATCH', path: '/users/1'));

        self::assertSame(200, $response->status());
        self::assertJsonStringEqualsJsonString('{"updated":true}', $response->body());
    }

    public function testMethodAttributeAcceptsHttpMethodEnum(): void
    {
        $attribute = new Method(HttpMethod::Put);
        $response = $attribute->validate(new Request(method: 'POST', path: '/users/1'));

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(405, $response->status());
        self::assertSame('PUT', $response->headers()['Allow']);
    }

    public function testRejectsInvalidHttpMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Request(method: 'TRACE', path: '/users');
    }
}
