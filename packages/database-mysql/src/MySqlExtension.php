<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabaseMySql;

use Bifrost\Extension\DatabasePdo\PdoConnectionFactory;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;
use InvalidArgumentException;

final class MySqlExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

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
                throw new InvalidArgumentException("Configuracao MySQL '$name' deve ser um array.");
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
            throw new InvalidArgumentException('A configuracao MySQL deve informar host e database.');
        }

        $port = (int) ($config['port'] ?? 3306);
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        $config['dsn'] = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";

        return $config;
    }
}
