<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LogExtensionConfigurationTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('LOG_DRIVER');
        putenv('MONGO_LOG_URI');
        putenv('MONGO_LOG_HOST');
        putenv('MONGO_LOG_PORT');
        putenv('MONGO_LOG_DATABASE');
        putenv('MONGO_LOG_COLLECTION');
        putenv('MONGO_LOG_USERNAME');
        putenv('MONGO_LOG_PASSWORD');
    }

    public function testRejectsUnsupportedLogDriver(): void
    {
        putenv('LOG_DRIVER=syslog');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('LOG_DRIVER deve ser stdout, file ou mongodb.');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRequiresInstalledPackageForConfiguredStdoutLog(): void
    {
        putenv('LOG_DRIVER=stdout');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instale bifrost/log-stdout');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRequiresInstalledPackageForConfiguredFileLog(): void
    {
        putenv('LOG_DRIVER=file');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instale bifrost/log-file');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRequiresInstalledPackageForConfiguredMongoLog(): void
    {
        putenv('LOG_DRIVER=mongodb');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instale bifrost/log-mongodb');

        require dirname(__DIR__) . '/config/extensions.php';
    }
}
