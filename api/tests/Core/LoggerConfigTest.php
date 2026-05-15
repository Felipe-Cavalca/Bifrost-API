<?php

declare(strict_types=1);

use Bifrost\Core\LoggerConfig;
use Bifrost\Core\Settings;
use PHPUnit\Framework\TestCase;

final class LoggerConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('BFR_API_LOG_DRIVER');
        putenv('BFR_API_LOG_FILE');
        putenv('BFR_API_LOG_COLLECTION');
    }

    public function testReadsConfiguredLoggerValues(): void
    {
        putenv('BFR_API_LOG_DRIVER=file');
        putenv('BFR_API_LOG_FILE=/tmp/bifrost.log');
        putenv('BFR_API_LOG_COLLECTION=application_logs');

        $config = LoggerConfig::fromSettings(new Settings());

        self::assertSame('file', $config->driver());
        self::assertSame('/tmp/bifrost.log', $config->file());
        self::assertSame('application_logs', $config->collection());
    }

    public function testUsesDefaultCollection(): void
    {
        putenv('BFR_API_LOG_DRIVER=mongo');
        putenv('BFR_API_LOG_COLLECTION');

        $config = LoggerConfig::fromSettings(new Settings());

        self::assertSame('logs', $config->collection());
    }
}
