<?php

namespace Bifrost\Core;

use Bifrost\Integration\Cache\RedisCache;
use Bifrost\Interface\Cache as CacheInterface;
use Bifrost\Interface\Insertable;

class Cache extends RedisCache implements CacheInterface
{
    /**
     * Gera uma chave de cache estável usando namespace e partes arbitrárias.
     */
    public static function buildKey(string $namespace, mixed ...$parts): string
    {
        $normalized = array_map(
            static function ($value): string {
                if ($value instanceof Insertable) {
                    $value = $value->value();
                }

                if (is_scalar($value) || $value === null) {
                    return (string) $value;
                }

                return serialize($value);
            },
            $parts
        );

        return "{$namespace}:" . md5(implode('|', $normalized));
    }
}
