<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis\Contracts;

use Bifrost\Extension\Redis\RedisConfig;
use Redis;

/**
 * Cria conexoes Redis para extensoes opcionais.
 */
interface RedisConnectionFactory
{
    /**
     * Retorna uma conexao Redis para a configuracao informada.
     */
    public function connect(RedisConfig $config): Redis;
}
