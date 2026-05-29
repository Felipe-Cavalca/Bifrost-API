<?php

declare(strict_types=1);

use Bifrost\Extension\LogStdout\Contracts\LogWriter;
use Bifrost\Extension\LogStdout\StdoutLogConfig;
use Bifrost\Extension\LogStdout\StdoutLogExtension;
use Bifrost\Extension\LogStdout\StdoutLogWriter;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\LogWriter as FrameworkLogWriter;
use Bifrost\Framework\Logging\Logger;
use PHPUnit\Framework\TestCase;

final class StdoutLogTest extends TestCase
{
    public function testWritesJsonLineToConfiguredCallback(): void
    {
        $lines = [];
        $writer = new StdoutLogWriter(
            StdoutLogConfig::fromArray(['stream' => 'stderr']),
            static function (string $line) use (&$lines): void {
                $lines[] = $line;
            }
        );

        $writer->write(['message' => 'teste', 'level' => 'info']);

        self::assertCount(1, $lines);
        self::assertJsonStringEqualsJsonString('{"message":"teste","level":"info"}', $lines[0]);
    }

    public function testExtensionRegistersLocalAndFrameworkContracts(): void
    {
        $fakeWriter = new class implements LogWriter {
            public function write(array $entry): void
            {
            }
        };
        $application = Application::create()->extend(new StdoutLogExtension(
            ['stream' => 'stdout'],
            static fn (StdoutLogConfig $config): LogWriter => $fakeWriter
        ));

        self::assertSame($fakeWriter, $application->container()->get(LogWriter::class));
        self::assertSame($fakeWriter, $application->container()->get(FrameworkLogWriter::class));
        self::assertInstanceOf(Logger::class, $application->container()->get(Logger::class));
    }

    public function testReusesSameWriterInstanceForLocalAndFrameworkContracts(): void
    {
        $fakeWriter = new class implements LogWriter {
            public function write(array $entry): void
            {
            }
        };
        $application = Application::create()->extend(new StdoutLogExtension(
            ['stream' => 'stdout'],
            static fn (StdoutLogConfig $config): LogWriter => $fakeWriter
        ));

        self::assertSame(
            $application->container()->get(LogWriter::class),
            $application->container()->get(FrameworkLogWriter::class)
        );
    }
}
