<?php

declare(strict_types=1);

use Bifrost\Class\HttpResponse;
use Bifrost\Enum\HttpStatusCode;
use Bifrost\Interface\Responseable;
use PHPUnit\Framework\TestCase;

final class HttpResponseTest extends TestCase
{
    public function testJsonSerializeUsesExplicitMessageAndAdditionalInfo(): void
    {
        $response = HttpResponse::success('done', ['ok' => true]);
        $response->addAditionalInfo(['traceId' => 'abc']);

        self::assertSame([
            'status' => 200,
            'message' => 'done',
            'data' => ['ok' => true],
            'errors' => null,
            'traceId' => 'abc',
        ], $response->jsonSerialize());
    }

    public function testCreatedBuildsExpectedPayload(): void
    {
        $payload = new class implements Responseable {
            public function jsonSerialize(): array|string
            {
                return ['id' => 1];
            }
        };

        $response = HttpResponse::created('User', $payload);

        self::assertSame(HttpStatusCode::CREATED, $response->status);
        self::assertSame('User created successfully', $response->message);
        self::assertSame($payload, $response->jsonSerialize()['data']);
    }

    public function testNamedFactoriesProduceExpectedStatuses(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PATCH';

        self::assertSame(HttpStatusCode::NOT_FOUND, HttpResponse::notFound(['x' => 1])->status);
        self::assertSame(HttpStatusCode::METHOD_NOT_ALLOWED, HttpResponse::methodNotAllowed('x')->status);
        self::assertSame(HttpStatusCode::BAD_REQUEST, HttpResponse::badRequest(['x' => 1])->status);
        self::assertSame(HttpStatusCode::CONFLICT, HttpResponse::conflict(['x' => 1])->status);
        self::assertSame(HttpStatusCode::INTERNAL_SERVER_ERROR, HttpResponse::internalServerError(['x' => 1])->status);
        self::assertSame(HttpStatusCode::UNAUTHORIZED, HttpResponse::unauthorized('x')->status);
        self::assertSame(HttpStatusCode::FORBIDDEN, HttpResponse::forbidden('x')->status);
        self::assertSame(['GET', 'POST'], HttpResponse::options(['GET', 'POST'])->jsonSerialize()['methods']);
    }
}
