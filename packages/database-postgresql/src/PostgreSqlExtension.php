<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePostgreSql;

use Bifrost\Extension\DatabasePdo\PdoConnectionFactory;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;
use InvalidArgumentException;

final class PostgreSqlExtension implements Extension
{
    /**
     * @param array<string, mixed> $config Configuracao PostgreSQL unica ou mapa de conexoes.
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * Registra a factory PDO configurada para PostgreSQL.
     */
    public function register(Application $application): void
    {
        $application->container()->bind(
            DatabaseConnectionFactory::class,
            new PdoConnectionFactory(config: $this->pdoConfig())
        );
    }

    private function pdoConfig(): array
    {
        if (!isset($this->config['connections'])) {
            return $this->withDsn($this->config);
        }

        $connections = [];
        foreach ($this->config['connections'] as $name => $config) {
            if (!is_array($config)) {
                throw new InvalidArgumentException("Configuracao PostgreSQL '$name' deve ser um array.");
            }

            $connections[$name] = $this->withDsn($config);
        }

        return ['connections' => $connections];
    }

    private function withDsn(array $config): array
    {
        $host = $config['host'] ?? null;
        $database = $config['database'] ?? null;

        if (!is_string($host) || $host === '' || !is_string($database) || $database === '') {
            throw new InvalidArgumentException('A configuracao PostgreSQL deve informar host e database.');
        }

        $port = (int) ($config['port'] ?? 5432);
        $config['dsn'] = "pgsql:host=$host;port=$port;dbname=$database";

        return $config;
    }
}
