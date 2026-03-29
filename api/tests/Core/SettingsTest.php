<?php

declare(strict_types=1);

use Bifrost\Core\AppError;
use Bifrost\Core\Settings;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    public function testReadsEnvironmentVariablesAndDatabaseSettings(): void
    {
        putenv('BFR_API_DB_DRIVER=sqlite');
        putenv('BFR_API_DB_NAME=' . bifrost_sqlite_path());

        $settings = new Settings();
        $config = $settings->getSettingsDatabase();

        self::assertSame('sqlite', $settings->BFR_API_DB_DRIVER);
        self::assertSame('sqlite', $config['driver']);
        self::assertSame(bifrost_sqlite_path(), $config['database']);
        self::assertNull($config['host']);
    }

    public function testLegacySqlEnvironmentVariablesStillWork(): void
    {
        putenv('BFR_API_SQL_DRIVER=sqlite');
        putenv('BFR_API_SQL_DATABASE=' . bifrost_sqlite_path());

        $settings = new Settings();
        $config = $settings->getSettingsDatabase();

        self::assertSame('sqlite', $config['driver']);
        self::assertSame(bifrost_sqlite_path(), $config['database']);
    }

    public function testMissingRequiredEnvironmentVariableThrowsAppError(): void
    {
        putenv('BFR_API_TEST_SQL_DRIVER');

        $settings = new Settings();

        $this->expectException(AppError::class);
        $settings->getSettingsDatabase('test');
    }
}
