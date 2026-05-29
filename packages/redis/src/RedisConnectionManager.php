<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Redis;

/**
 * Reutiliza conexoes Redis por configuracao.
 */
final class RedisConnectionManager implements RedisConnectionFactory
{
    /** @var array<string, Redis> */
    private array $connections = [];

    /**
     * @param RedisConnectionFactory $factory Factory concreta usada ao abrir conexoes novas.
     */
    public function __construct(
        private readonly RedisConnectionFactory $factory = new NativeRedisConnectionFactory()
    ) {
    }

    /**
     * Retorna uma conexao Redis reutilizada para a configuracao informada.
     */
    public function connect(RedisConfig $config): Redis
    {
        $key = $config->fingerprint();

        return $this->connections[$key] ??= $this->factory->connect($config);
    }
}
