<?php

declare(strict_types=1);

use Bifrost\Controller\Index;
use Bifrost\Enum\HttpStatusCode;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testHealthReturnsHealthyPayload(): void
    {
        $response = (new Index())->health();
        $payload = $response->jsonSerialize();

        self::assertSame(HttpStatusCode::OK, $response->status);
        self::assertSame('OK', $response->message);
        self::assertSame('healthy', $payload['data']['status']);
        self::assertSame('bifrost-api', $payload['data']['service']);
        self::assertArrayHasKey('checked_at', $payload['data']);
    }
}
