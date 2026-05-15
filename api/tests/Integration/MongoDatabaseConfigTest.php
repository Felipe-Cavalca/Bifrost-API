<?php

declare(strict_types=1);

use Bifrost\Core\Settings;
use Bifrost\Integration\Database\MongoDatabaseConfig;
use PHPUnit\Framework\TestCase;

final class MongoDatabaseConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('BFR_API_MONGO_URI');
        putenv('BFR_API_MONGO_HOST');
        putenv('BFR_API_MONGO_PORT');
        putenv('BFR_API_MONGO_DATABASE');
        putenv('BFR_API_MONGO_USER');
        putenv('BFR_API_MONGO_PASSWORD');
    }

    public function testUsesConfiguredUriWhenAvailable(): void
    {
        putenv('BFR_API_MONGO_URI=mongodb://mongo:27017');
        putenv('BFR_API_MONGO_HOST=ignored');
        putenv('BFR_API_MONGO_PORT=27018');
        putenv('BFR_API_MONGO_DATABASE=bifrost_logs');

        $config = MongoDatabaseConfig::fromSettings(new Settings());

        self::assertSame('mongodb://mongo:27017', $config->uri());
        self::assertSame('bifrost_logs', $config->database());
    }

    public function testBuildsUriFromHostPortAndCredentials(): void
    {
        putenv('BFR_API_MONGO_HOST=mongo');
        putenv('BFR_API_MONGO_PORT=27018');
        putenv('BFR_API_MONGO_USER=bifrost');
        putenv('BFR_API_MONGO_PASSWORD=secret value');

        $config = MongoDatabaseConfig::fromSettings(new Settings());

        self::assertSame('mongodb://bifrost:secret%20value@mongo:27018', $config->uri());
    }

    public function testUsesDefaultPort(): void
    {
        putenv('BFR_API_MONGO_HOST=mongo');
        putenv('BFR_API_MONGO_PORT');

        $config = MongoDatabaseConfig::fromSettings(new Settings());

        self::assertSame('mongodb://mongo:27017', $config->uri());
    }
}
