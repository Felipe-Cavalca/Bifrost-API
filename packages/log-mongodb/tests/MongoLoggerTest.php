<?php

declare(strict_types=1);

use Bifrost\Extension\LogMongoDb\Contracts\LogWriter;
use Bifrost\Extension\LogMongoDb\MongoLogConfig;
use Bifrost\Extension\LogMongoDb\MongoLogExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Logging\Logger;
use PHPUnit\Framework\TestCase;

final class MongoLoggerTest extends TestCase
{
    public function testExtensionRegistersFrameworkLogger(): void
    {
        $fakeWriter = new class implements LogWriter {
            public array $entries = [];

            public function write(array $entry): void
            {
                $this->entries[] = $entry;
            }
        };
        $application = Application::create()->extend(new MongoLogExtension(
            [
                'uri' => 'mongodb://mongo:27017',
                'database' => 'bifrost_logs',
            ],
            static fn (MongoLogConfig $config): LogWriter => $fakeWriter
        ));

        $logger = $application->container()->get(Logger::class);

        self::assertInstanceOf(Logger::class, $logger);
    }
}
