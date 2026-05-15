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

        $config = self::normalizeConfig($config);

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
        return self::normalizeConfig($settings->getSettingsMongo());
    }

    private static function buildUri(array $config): string
    {
        $config = self::normalizeConfig($config);

        if (!empty($config['uri'])) {
            return (string) $config['uri'];
        }

        if (empty($config['host'])) {
            throw new \InvalidArgumentException('Mongo host is required.');
        }

        return 'mongodb://' . self::auth($config) . $config['host'] . ':' . $config['port'];
    }

    private static function normalizeConfig(array $config): array
    {
        return [
            'uri' => self::optionalString($config['uri'] ?? null),
            'host' => self::optionalString($config['host'] ?? null),
            'port' => self::optionalString($config['port'] ?? null) ?? '27017',
            'database' => self::optionalString($config['database'] ?? null),
            'username' => self::optionalString($config['username'] ?? null),
            'password' => self::optionalString($config['password'] ?? null),
        ];
    }

    private static function auth(array $config): string
    {
        if (empty($config['username']) && empty($config['password'])) {
            return '';
        }

        return rawurlencode((string) $config['username'])
            . ':'
            . rawurlencode((string) $config['password'])
            . '@';
    }

    private static function optionalString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function assertExtensionAvailable(): void
    {
        if (!class_exists(\MongoDB\Driver\Manager::class)) {
            throw new \RuntimeException('MongoDB extension not found. Install the mongodb PHP extension.');
        }
    }
}
