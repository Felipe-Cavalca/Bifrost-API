<?php

namespace Bifrost\Integration\Database;

use Bifrost\Core\Settings;
use Bifrost\Interface\NoSqlDatabase;

class MongoDatabase implements NoSqlDatabase
{
    private \MongoDB\Driver\Manager $manager;
    private string $database;

    public function __construct(array $config, ?\MongoDB\Driver\Manager $manager = null)
    {
        self::assertExtensionAvailable();

        $this->database = (string) ($config['database'] ?? '');
        if ($this->database === '') {
            throw new \InvalidArgumentException('Mongo database is required.');
        }

        $this->manager = $manager ?? new \MongoDB\Driver\Manager(self::buildUri($config));
    }

    public static function fromSettings(?Settings $settings = null): self
    {
        $settings ??= new Settings();

        return new self(self::buildConfigFromSettings($settings));
    }

    public function insertOne(string $collection, array $document): void
    {
        if ($collection === '') {
            throw new \InvalidArgumentException('Mongo collection is required.');
        }

        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->insert($document);

        $this->manager->executeBulkWrite(
            "{$this->database}.{$collection}",
            $bulk,
            [
                'writeConcern' => new \MongoDB\Driver\WriteConcern(
                    \MongoDB\Driver\WriteConcern::MAJORITY,
                    1000
                ),
            ]
        );
    }

    public function getManager(): \MongoDB\Driver\Manager
    {
        return $this->manager;
    }

    protected static function buildConfigFromSettings(Settings $settings): array
    {
        return $settings->getSettingsMongo();
    }

    private static function buildUri(array $config): string
    {
        if (!empty($config['uri'])) {
            return (string) $config['uri'];
        }

        $host = (string) ($config['host'] ?? '');
        if ($host === '') {
            throw new \InvalidArgumentException('Mongo host is required.');
        }

        $port = (string) ($config['port'] ?? '27017');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $auth = '';

        if ($username !== '' || $password !== '') {
            $auth = rawurlencode($username) . ':' . rawurlencode($password) . '@';
        }

        return "mongodb://{$auth}{$host}:{$port}";
    }

    private static function assertExtensionAvailable(): void
    {
        if (!class_exists(\MongoDB\Driver\Manager::class)) {
            throw new \RuntimeException('MongoDB extension not found. Install the mongodb PHP extension.');
        }
    }
}
