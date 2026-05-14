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

    public function testMissingRequiredEnvironmentVariableThrowsAppError(): void
    {
        putenv('BFR_API_TEST_DB_DRIVER');

        $settings = new Settings();

        $this->expectException(AppError::class);
        $settings->getSettingsDatabase('test');
    }

    public function testReadsMongoSettings(): void
    {
        putenv('BFR_API_MONGO_URI=mongodb://mongo:27017');
        putenv('BFR_API_MONGO_HOST=mongo');
        putenv('BFR_API_MONGO_PORT=27018');
        putenv('BFR_API_MONGO_DATABASE=bifrost_logs');
        putenv('BFR_API_MONGO_USER=bifrost');
        putenv('BFR_API_MONGO_PASSWORD=secret');

        $settings = new Settings();
        $config = $settings->getSettingsMongo();

        self::assertSame('mongodb://mongo:27017', $config['uri']);
        self::assertSame('mongo', $config['host']);
        self::assertSame('27018', $config['port']);
        self::assertSame('bifrost_logs', $config['database']);
        self::assertSame('bifrost', $config['username']);
        self::assertSame('secret', $config['password']);

        putenv('BFR_API_MONGO_URI');
        putenv('BFR_API_MONGO_HOST');
        putenv('BFR_API_MONGO_PORT');
        putenv('BFR_API_MONGO_DATABASE');
        putenv('BFR_API_MONGO_USER');
        putenv('BFR_API_MONGO_PASSWORD');
    }
}
