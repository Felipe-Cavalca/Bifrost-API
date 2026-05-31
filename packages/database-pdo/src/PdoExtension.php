<?php

declare(strict_types=1);

namespace Bifrost\Extension\DatabasePdo;

use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use Bifrost\Framework\Contracts\Extension;

final class PdoExtension implements Extension
{
    public function __construct(private readonly array $config)
    {
    }

    public function register(Application $application): void
    {
        $application->container()->bind(
            DatabaseConnectionFactory::class,
            new PdoConnectionFactory(config: $this->config)
        );
    }
}
