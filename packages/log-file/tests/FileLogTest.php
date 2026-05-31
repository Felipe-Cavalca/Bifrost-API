<?php

declare(strict_types=1);

use Bifrost\Extension\LogFile\Contracts\LogWriter;
use Bifrost\Extension\LogFile\FileLogConfig;
use Bifrost\Extension\LogFile\FileLogExtension;
use Bifrost\Extension\LogFile\FileLogWriter;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\LogWriter as FrameworkLogWriter;
use Bifrost\Framework\Logging\Logger;
use PHPUnit\Framework\TestCase;

final class FileLogTest extends TestCase
{
    public function testWritesJsonLineToConfiguredFile(): void
    {
        $path = sys_get_temp_dir() . '/bifrost-file-log-test-' . bin2hex(random_bytes(4)) . '/app.log';
        $writer = new FileLogWriter(FileLogConfig::fromArray(['path' => $path]));

        $writer->write(['message' => 'teste', 'level' => 'info']);

        self::assertFileExists($path);
        self::assertJsonStringEqualsJsonString(
            '{"message":"teste","level":"info"}',
            trim((string) file_get_contents($path))
        );
    }

    public function testExtensionRegistersLocalAndFrameworkContracts(): void
    {
        $fakeWriter = new class implements LogWriter {
            public function write(array $entry): void
            {
            }
        };
        $application = Application::create()->extend(new FileLogExtension(
            ['path' => sys_get_temp_dir() . '/bifrost-file.log'],
            static fn (FileLogConfig $config): LogWriter => $fakeWriter
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
        $application = Application::create()->extend(new FileLogExtension(
            ['path' => sys_get_temp_dir() . '/bifrost-file.log'],
            static fn (FileLogConfig $config): LogWriter => $fakeWriter
        ));

        self::assertSame(
            $application->container()->get(LogWriter::class),
            $application->container()->get(FrameworkLogWriter::class)
        );
    }
}
