<?php

declare(strict_types=1);

use Bifrost\Extension\LogMongoDb\MongoLogConfig;
use PHPUnit\Framework\TestCase;

final class MongoLogConfigTest extends TestCase
{
    public function testBuildsLegacyCompatibleUriAndNamespace(): void
    {
        $config = MongoLogConfig::fromArray([
            'host' => 'mongo',
            'port' => 27018,
            'database' => 'bifrost_logs',
            'collection' => 'application_logs',
            'username' => 'bifrost',
            'password' => 'secret value',
        ]);

        self::assertSame('mongodb://bifrost:secret%20value@mongo:27018', $config->uri());
        self::assertSame('bifrost_logs.application_logs', $config->collectionNamespace());
    }

    public function testUsesUriAndDefaultCollection(): void
    {
        $config = MongoLogConfig::fromArray([
            'uri' => 'mongodb://mongo:27017',
            'database' => 'bifrost_logs',
        ]);

        self::assertSame('mongodb://mongo:27017', $config->uri());
        self::assertSame('logs', $config->collection());
    }

    public function testUsesLegacyDefaultPortWhenBuildingUriFromHost(): void
    {
        $config = MongoLogConfig::fromArray([
            'host' => 'mongo',
            'database' => 'bifrost_logs',
        ]);

        self::assertSame('mongodb://mongo:27017', $config->uri());
    }

    public function testRequiresDatabaseName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MongoLogConfig::fromArray(['uri' => 'mongodb://mongo:27017']);
    }
}
