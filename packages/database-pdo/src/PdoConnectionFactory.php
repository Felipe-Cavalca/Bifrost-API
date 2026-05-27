<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use InvalidArgumentException;
use PDO;

final class PdoConnectionFactory implements DatabaseConnectionFactory
{
    /** @var array<string, PDO> */
    private array $connections = [];

    public function __construct(private readonly array $config)
    {
    }

    public function connection(?string $name = null): PDO
    {
        $connectionName = $name ?? 'default';

        if (!isset($this->connections[$connectionName])) {
            $config = $this->connectionConfig($name);
            $this->connections[$connectionName] = new PDO(
                dsn: $config['dsn'],
                username: $config['username'] ?? null,
                password: $config['password'] ?? null,
                options: $config['options'] ?? []
            );
        }

        return $this->connections[$connectionName];
    }

    private function connectionConfig(?string $name): array
    {
        if (isset($this->config['dsn'])) {
            if ($name !== null && $name !== 'default') {
                throw new InvalidArgumentException("Conexao PDO '$name' nao esta configurada.");
            }

            return $this->validatedConfig($this->config);
        }

        $connectionName = $name ?? 'default';
        $connection = $this->config['connections'][$connectionName] ?? null;

        if (!is_array($connection)) {
            throw new InvalidArgumentException("Conexao PDO '$connectionName' nao esta configurada.");
        }

        return $this->validatedConfig($connection);
    }

    private function validatedConfig(array $config): array
    {
        if (!isset($config['dsn']) || !is_string($config['dsn']) || $config['dsn'] === '') {
            throw new InvalidArgumentException('A configuracao PDO deve informar um DSN valido.');
        }

        return $config;
    }
}
