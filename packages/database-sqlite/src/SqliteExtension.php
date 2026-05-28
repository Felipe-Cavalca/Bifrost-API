<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabaseSqlite;

use Bifrost\Extension\DatabasePdo\PdoConnectionFactory;
use Bifrost\Extension\DatabasePdo\PdoDatabase;
use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;
use InvalidArgumentException;

final class SqliteExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

    public function register(Application $application): void
    {
        $factory = new PdoConnectionFactory(config: $this->pdoConfig());

        $application->container()->bind(
            DatabaseConnectionFactory::class,
            $factory
        );

        $application->container()->bind(
            PdoDatabase::class,
            static fn (Container $container): PdoDatabase => new PdoDatabase(
                connection: $container->get(DatabaseConnectionFactory::class)->connection()
            )
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
                throw new InvalidArgumentException("Configuracao SQLite '$name' deve ser um array.");
            }

            $connections[$name] = $this->withDsn($config);
        }

        return ['connections' => $connections];
    }

    private function withDsn(array $config): array
    {
        if (($config['memory'] ?? false) === true) {
            $config['dsn'] = 'sqlite::memory:';

            return $config;
        }

        $path = $config['path'] ?? null;
        if (!is_string($path) || $path === '') {
            throw new InvalidArgumentException('A configuracao SQLite deve informar path ou memory=true.');
        }

        $config['dsn'] = "sqlite:$path";

        return $config;
    }
}
