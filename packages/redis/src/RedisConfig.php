<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

/**
 * Configuracao de conexao Redis compartilhada por extensoes.
 */
final class RedisConfig
{
    /**
     * @param string $host Host Redis.
     * @param int $port Porta Redis.
     * @param float $timeout Timeout de conexao em segundos.
     * @param string|null $password Senha Redis opcional.
     * @param int|null $database Banco Redis opcional.
     */
    public function __construct(
        public readonly string $host = '127.0.0.1',
        public readonly int $port = 6379,
        public readonly float $timeout = 0.0,
        public readonly ?string $password = null,
        public readonly ?int $database = null
    ) {
    }

    /**
     * @param array{host?: string, port?: int|string, timeout?: float|int|string, password?: string|null, database?: int|string|null} $config
     */
    public static function fromArray(array $config): self
    {
        $password = $config['password'] ?? null;
        $database = $config['database'] ?? null;

        return new self(
            host: (string) ($config['host'] ?? '127.0.0.1'),
            port: (int) ($config['port'] ?? 6379),
            timeout: (float) ($config['timeout'] ?? 0.0),
            password: $password === null || $password === '' ? null : (string) $password,
            database: $database === null || $database === '' ? null : (int) $database
        );
    }

    /**
     * Chave interna usada para reutilizar conexoes equivalentes.
     */
    public function fingerprint(): string
    {
        return hash('sha256', json_encode([
            'host' => $this->host,
            'port' => $this->port,
            'timeout' => $this->timeout,
            'password' => $this->password,
            'database' => $this->database,
        ], JSON_THROW_ON_ERROR));
    }
}
