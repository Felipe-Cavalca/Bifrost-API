<?php

declare(strict_types=1);

use Bifrost\Extension\LogMongoDb\MongoLogConfig;
use Bifrost\Extension\LogMongoDb\MongoLogWriter;
use PHPUnit\Framework\TestCase;

final class MongoLogWriterTest extends TestCase
{
    public function testWritesDocumentWithoutChangingLegacyLogShape(): void
    {
        $namespace = null;
        $persistedEntry = null;
        $entry = [
            'timestamp' => '2026-05-27T12:00:00+00:00',
            'level' => 'error',
            'message' => 'Mongo log',
            'request_id' => '123e4567-e89b-12d3-a456-426614174000',
            'context' => ['foo' => 'bar'],
        ];
        $writer = new MongoLogWriter(
            MongoLogConfig::fromArray([
                'uri' => 'mongodb://mongo:27017',
                'database' => 'bifrost_logs',
                'collection' => 'application_logs',
            ]),
            static function (string $target, array $document) use (&$namespace, &$persistedEntry): void {
                $namespace = $target;
                $persistedEntry = $document;
            }
        );

        $writer->write($entry);

        self::assertSame('bifrost_logs.application_logs', $namespace);
        self::assertSame($entry, $persistedEntry);
    }
}
