<?php

declare(strict_types=1);

use Bifrost\Extension\LogMongoDb\Contracts\LogWriter;
use Bifrost\Extension\LogMongoDb\MongoLogConfig;
use Bifrost\Extension\LogMongoDb\MongoLogExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\LogWriter as FrameworkLogWriter;
use PHPUnit\Framework\TestCase;

final class FrameworkLogWriterContractTest extends TestCase
{
    public function testRegistersFrameworkLogWriterContract(): void
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

        $writer = $application->container()->get(FrameworkLogWriter::class);
        $writer->write(['message' => 'teste']);

        self::assertSame($fakeWriter, $writer);
        self::assertSame([['message' => 'teste']], $fakeWriter->entries);
    }

    public function testReusesSameWriterInstanceForLocalAndFrameworkContracts(): void
    {
        $fakeWriter = new class implements LogWriter {
            public function write(array $entry): void
            {
            }
        };
        $application = Application::create()->extend(new MongoLogExtension(
            [
                'uri' => 'mongodb://mongo:27017',
                'database' => 'bifrost_logs',
            ],
            static fn (MongoLogConfig $config): LogWriter => $fakeWriter
        ));

        self::assertSame(
            $application->container()->get(LogWriter::class),
            $application->container()->get(FrameworkLogWriter::class)
        );
    }
}
