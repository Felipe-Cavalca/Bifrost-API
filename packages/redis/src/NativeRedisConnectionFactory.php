<?php

declare(strict_types=1);

namespace Bifrost\Extension\Redis;

use Bifrost\Extension\Redis\Contracts\RedisConnectionFactory;
use Redis;
use RuntimeException;

/**
 * Factory baseada na extensao nativa ext-redis.
 */
final class NativeRedisConnectionFactory implements RedisConnectionFactory
{
    public function connect(RedisConfig $config): Redis
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

        return $redis;
    }
}
