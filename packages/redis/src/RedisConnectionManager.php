<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisClient;
use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;

/**
 * Reutiliza clientes Redis por configuracao.
 */
final class RedisConnectionManager implements RedisConnectionFactory
{
    /** @var array<string, RedisClient> */
    private array $connections = [];

    /**
     * @param RedisConnectionFactory $factory Factory concreta usada ao abrir conexoes novas.
     */
    public function __construct(
        private readonly RedisConnectionFactory $factory = new NativeRedisConnectionFactory()
    ) {
    }

    /**
     * Retorna um cliente Redis reutilizado para a configuracao informada.
     */
    public function connect(RedisConfig $config): RedisClient
    {
        $key = $config->fingerprint();

        return $this->connections[$key] ??= $this->factory->connect($config);
    }
}
