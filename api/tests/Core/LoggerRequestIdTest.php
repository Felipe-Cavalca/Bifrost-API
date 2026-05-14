<?php

declare(strict_types=1);

use Bifrost\Core\Logger;
use Bifrost\DataTypes\UUID;
use PHPUnit\Framework\TestCase;

final class LoggerRequestIdTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_X_REQUEST_ID']);
        Logger::resetRequestId();
    }

    public function testUsesIncomingRequestIdHeader(): void
    {
        $_SERVER['HTTP_X_REQUEST_ID'] = '123e4567-e89b-12d3-a456-426614174000';
        Logger::resetRequestId();

        self::assertSame('123e4567-e89b-12d3-a456-426614174000', Logger::requestId()->value());
    }

    public function testGeneratesRequestIdWhenHeaderIsMissing(): void
    {
        Logger::resetRequestId();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            Logger::requestId()->value()
        );
    }

    public function testIgnoresInvalidRequestIdHeader(): void
    {
        $_SERVER['HTTP_X_REQUEST_ID'] = "bad\nheader";
        Logger::resetRequestId();

        self::assertNotSame($_SERVER['HTTP_X_REQUEST_ID'], Logger::requestId()->value());
        self::assertInstanceOf(UUID::class, Logger::requestId());
    }
}
