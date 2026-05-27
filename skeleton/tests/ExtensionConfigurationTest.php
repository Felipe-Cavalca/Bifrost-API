<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ExtensionConfigurationTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('CACHE_DRIVER');
        putenv('DB_DRIVER');
    }

    public function testRejectsUnsupportedCacheDriver(): void
    {
        putenv('CACHE_DRIVER=memcached');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CACHE_DRIVER deve ser apcu ou redis.');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRequiresInstalledPackageForConfiguredCache(): void
    {
        putenv('CACHE_DRIVER=apcu');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instale bifrost/cache-apcu');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRejectsUnsupportedDatabaseDriver(): void
    {
        putenv('DB_DRIVER=oracle');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_DRIVER deve ser mysql ou postgresql.');

        require dirname(__DIR__) . '/config/extensions.php';
    }

    public function testRequiresInstalledPackageForConfiguredDatabase(): void
    {
        putenv('DB_DRIVER=mysql');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Instale bifrost/database-mysql');

        require dirname(__DIR__) . '/config/extensions.php';
    }
}
