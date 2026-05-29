<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Extension;

/**
 * Registra a factory Redis reutilizavel no container.
 */
final class RedisExtension implements Extension
{
    public function __construct(private readonly ?RedisConnectionFactory $factory = null)
    {
    }

    public function register(Application $application): void
    {
        RedisServiceRegistrar::register($application, $this->factory);
    }
}
