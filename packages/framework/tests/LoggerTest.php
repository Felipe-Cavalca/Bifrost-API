<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Contracts\LogWriter;
use Bifrost\Framework\Logging\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LoggerTest extends TestCase
{
    public function testWritesStructuredLogEntry(): void
    {
        $writer = new RecordingLogWriter();
        $logger = new Logger(
            writer: $writer,
            timestampResolver: static fn (): string => '2026-05-28T12:00:00+00:00',
            requestIdResolver: static fn (): string => 'req-123'
        );

        $logger->info('Requisicao recebida', ['route' => '/health']);

        self::assertSame([[
            'timestamp' => '2026-05-28T12:00:00+00:00',
            'level' => 'info',
            'message' => 'Requisicao recebida',
            'request_id' => 'req-123',
            'context' => ['route' => '/health'],
        ]], $writer->entries);
    }

    public function testWritesExceptionShapeWithoutSensitiveMessage(): void
    {
        $writer = new RecordingLogWriter();
        $logger = new Logger(
            writer: $writer,
            timestampResolver: static fn (): string => '2026-05-28T12:00:00+00:00',
            requestIdResolver: static fn (): string => 'req-123'
        );

        $logger->exception(new RuntimeException('senha=secret', 500), ['operation' => 'store']);

        self::assertSame('error', $writer->entries[0]['level']);
        self::assertSame('Unhandled exception', $writer->entries[0]['message']);
        self::assertSame(RuntimeException::class, $writer->entries[0]['context']['exception']['class']);
        self::assertSame(500, $writer->entries[0]['context']['exception']['code']);
        self::assertStringNotContainsString('secret', json_encode($writer->entries[0], JSON_THROW_ON_ERROR));
    }
}

final class RecordingLogWriter implements LogWriter
{
    /** @var list<array<string, mixed>> */
    public array $entries = [];

    public function write(array $entry): void
    {
        $this->entries[] = $entry;
    }
}
