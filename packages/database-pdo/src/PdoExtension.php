<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\TransactionManager;

final class PdoExtension implements Extension
{
    /**
     * @param array<string, mixed> $config Configuracao PDO unica ou mapa de conexoes.
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * Registra factory PDO, banco padrao e gerenciador de transacao.
     */
    public function register(Application $application): void
    {
        $factory = new PdoConnectionFactory(config: $this->config);

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

        $application->container()->bind(
            TransactionManager::class,
            static fn (Container $container): TransactionManager => $container->get(PdoDatabase::class)
        );
    }
}
