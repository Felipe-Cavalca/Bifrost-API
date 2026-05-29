<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogMongoDb;

use InvalidArgumentException;

final class MongoLogConfig
{
    private function __construct(
        private readonly string $uri,
        private readonly string $database,
        private readonly string $collection
    ) {
    }

    /**
     * Cria a configuracao a partir de array.
     *
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $database = self::optionalString($config['database'] ?? null);
        if ($database === null) {
            throw new InvalidArgumentException('O banco MongoDB de logs e obrigatorio.');
        }

        $collection = self::optionalString($config['collection'] ?? null) ?? 'logs';

        return new self(
            uri: self::buildUri($config),
            database: $database,
            collection: $collection
        );
    }

    /**
     * Retorna a URI de conexao MongoDB.
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Retorna o banco onde os logs serao gravados.
     */
    public function database(): string
    {
        return $this->database;
    }

    /**
     * Retorna a collection onde os logs serao gravados.
     */
    public function collection(): string
    {
        return $this->collection;
    }

    /**
     * Retorna namespace MongoDB no formato database.collection.
     */
    public function collectionNamespace(): string
    {
        return "{$this->database}.{$this->collection}";
    }

    private static function buildUri(array $config): string
    {
        $uri = self::optionalString($config['uri'] ?? null);
        if ($uri !== null) {
            return $uri;
        }

        $host = self::optionalString($config['host'] ?? null);
        if ($host === null) {
            throw new InvalidArgumentException('O host MongoDB de logs e obrigatorio quando a URI nao e informada.');
        }

        $port = self::optionalPort($config['port'] ?? null);
        $username = self::optionalString($config['username'] ?? null);
        $password = self::optionalString($config['password'] ?? null);

        return 'mongodb://' . self::auth($username, $password) . $host . ':' . $port;
    }

    private static function auth(?string $username, ?string $password): string
    {
        if ($username === null && $password === null) {
            return '';
        }

        return rawurlencode($username ?? '') . ':' . rawurlencode($password ?? '') . '@';
    }

    private static function optionalPort(mixed $value): string
    {
        if (is_int($value) && $value > 0) {
            return (string) $value;
        }

        return self::optionalString($value) ?? '27017';
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
