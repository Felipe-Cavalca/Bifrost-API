<?php

declare(strict_types=1);

namespace Bifrost\Integration\Database;

use Bifrost\Core\Settings;

class MongoDatabaseConfig
{
    public function __construct(
        private readonly ?string $uri,
        private readonly ?string $host,
        private readonly string $port,
        private readonly ?string $database,
        private readonly ?string $username,
        private readonly ?string $password
    ) {
    }

    public static function fromSettings(?Settings $settings = null): self
    {
        $settings ??= new Settings();
        $config = $settings->getSettingsMongo();

        return new self(
            uri: self::optionalString($config['uri'] ?? null),
            host: self::optionalString($config['host'] ?? null),
            port: self::optionalString($config['port'] ?? null) ?? '27017',
            database: self::optionalString($config['database'] ?? null),
            username: self::optionalString($config['username'] ?? null),
            password: self::optionalString($config['password'] ?? null)
        );
    }

    public function database(): ?string
    {
        return $this->database;
    }

    public function uri(): string
    {
        if ($this->uri !== null) {
            return $this->uri;
        }

        if ($this->host === null) {
            throw new \InvalidArgumentException('Mongo host is required.');
        }

        return 'mongodb://' . $this->auth() . $this->host . ':' . $this->port;
    }

    public function toArray(): array
    {
        return [
            'uri' => $this->uri,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    private function auth(): string
    {
        if ($this->username === null && $this->password === null) {
            return '';
        }

        return rawurlencode((string) $this->username)
            . ':'
            . rawurlencode((string) $this->password)
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
}
