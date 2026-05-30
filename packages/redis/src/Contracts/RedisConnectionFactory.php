<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis\Contracts;

use Bifrost\Extension\Redis\RedisConfig;

/**
 * Cria clientes Redis para extensoes opcionais.
 */
interface RedisConnectionFactory
{
    /**
     * Retorna um cliente Redis para a configuracao informada.
     */
    public function connect(RedisConfig $config): RedisClient;
}
