<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisClient;
use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Redis;
use RuntimeException;

/**
 * Factory baseada na extensao nativa ext-redis.
 */
final class NativeRedisConnectionFactory implements RedisConnectionFactory
{
    /**
     * Abre uma conexao Redis usando ext-redis e retorna o adapter Bifrost.
     */
    public function connect(RedisConfig $config): RedisClient
    {
        $redis = new Redis();
        $connected = $redis->connect($config->host, $config->port, $config->timeout);

        if (!$connected) {
            throw new RuntimeException('Nao foi possivel conectar ao Redis.');
        }

        if ($config->password !== null) {
            $redis->auth($config->password);
        }

        if ($config->database !== null) {
            $redis->select($config->database);
        }

        return new NativeRedisClient($redis);
    }
}
