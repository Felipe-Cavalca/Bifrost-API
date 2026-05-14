<?php

declare(strict_types=1);

use Bifrost\Core\Logger;
use Bifrost\DataTypes\UUID;
use Bifrost\Interface\NoSqlDatabase;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $this->logFile = tempnam(sys_get_temp_dir(), 'bifrost-log-');
        putenv('BFR_API_LOG_DRIVER=file');
        putenv('BFR_API_LOG_FILE=' . $this->logFile);
    }

    protected function tearDown(): void
    {
        putenv('BFR_API_LOG_DRIVER=none');
        putenv('BFR_API_LOG_FILE');
        putenv('BFR_API_LOG_COLLECTION');
        Logger::setNoSqlDatabase(null);
        Logger::resetRequestId();

        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testLoggerWritesStructuredJsonToFile(): void
    {
        Logger::resetRequestId(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        Logger::info('Test log', ['foo' => 'bar']);
        $output = file_get_contents($this->logFile);

        self::assertIsString($output);
        $payload = json_decode(trim($output), true);

        self::assertSame('info', $payload['level']);
        self::assertSame('Test log', $payload['message']);
        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $payload['request_id']);
        self::assertSame(['foo' => 'bar'], $payload['context']);
        self::assertArrayHasKey('timestamp', $payload);
    }

    public function testNoneDriverDoesNotWriteOrGenerateRequestId(): void
    {
        putenv('BFR_API_LOG_DRIVER=none');
        Logger::resetRequestId();

        Logger::error('Ignored log');

        self::assertSame('', file_get_contents($this->logFile));

        $property = new ReflectionProperty(Logger::class, 'requestId');
        $property->setAccessible(true);

        self::assertNull($property->getValue());
    }

    public function testLoggerWritesStructuredJsonToMongoWhenConfigured(): void
    {
        $database = new class implements NoSqlDatabase {
            public string $collection = '';
            public array $document = [];

            public function insertOne(string $collection, array $document): void
            {
                $this->collection = $collection;
                $this->document = $document;
            }
        };

        putenv('BFR_API_LOG_DRIVER=mongo');
        putenv('BFR_API_LOG_COLLECTION=application_logs');
        Logger::setNoSqlDatabase($database);
        Logger::resetRequestId(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        Logger::error('Mongo log', ['foo' => 'bar']);

        self::assertSame('application_logs', $database->collection);
        self::assertSame('error', $database->document['level']);
        self::assertSame('Mongo log', $database->document['message']);
        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $database->document['request_id']);
        self::assertSame(['foo' => 'bar'], $database->document['context']);
        self::assertArrayHasKey('timestamp', $database->document);
        self::assertSame('', file_get_contents($this->logFile));
    }
}
