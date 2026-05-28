<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Container;

final class PdoExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

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
    }
}
