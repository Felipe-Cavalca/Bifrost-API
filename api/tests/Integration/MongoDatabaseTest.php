<?php

declare(strict_types=1);

use Bifrost\Core\Settings;
use Bifrost\Integration\Database\MongoDatabase;
use PHPUnit\Framework\TestCase;

final class MongoDatabaseTest extends TestCase
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

    public function testBuildsConfigFromSettingsLikeDatabaseAdapters(): void
    {
        putenv('BFR_API_MONGO_URI=mongodb://mongo:27017');
        putenv('BFR_API_MONGO_HOST=ignored');
        putenv('BFR_API_MONGO_PORT=27018');
        putenv('BFR_API_MONGO_DATABASE=bifrost_logs');

        $config = $this->buildConfigFromSettings();

        self::assertSame('mongodb://mongo:27017', $config['uri']);
        self::assertSame('bifrost_logs', $config['database']);
    }

    public function testBuildsUriFromHostPortAndCredentials(): void
    {
        $uri = $this->buildUri([
            'host' => 'mongo',
            'port' => '27018',
            'username' => 'bifrost',
            'password' => 'secret value',
            'database' => 'logs',
        ]);

        self::assertSame('mongodb://bifrost:secret%20value@mongo:27018', $uri);
    }

    public function testBuildsUriWithDefaultPort(): void
    {
        $uri = $this->buildUri([
            'host' => 'mongo',
            'database' => 'logs',
        ]);

        self::assertSame('mongodb://mongo:27017', $uri);
    }

    private function buildConfigFromSettings(): array
    {
        $method = new ReflectionMethod(MongoDatabase::class, 'buildConfigFromSettings');
        $method->setAccessible(true);

        return $method->invoke(null, new Settings());
    }

    private function buildUri(array $config): string
    {
        $method = new ReflectionMethod(MongoDatabase::class, 'buildUri');
        $method->setAccessible(true);

        return $method->invoke(null, $config);
    }
}
