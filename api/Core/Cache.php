<?php

namespace Bifrost\Core;

use Bifrost\Integration\Cache\RedisCache;
use Bifrost\Interface\Cache as CacheInterface;
use Bifrost\Interface\Insertable;

class Cache extends RedisCache implements CacheInterface
{

    /**
     * Gera a chave de cache no formato 'entidade:campo:valor'.
     *
     * @param array $conditions Condições utilizadas na busca
     * @return string Chave para o cache
     */
    public static function buildCacheKey(string $entity, array $conditions): string
    {
        $parts = [];
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if ($value instanceof Insertable) {
                $value = $value->value();
            }

            $parts[] = "{$field}:{$value}";
        }
        return $entity . ':' . implode(':', $parts);
    }
}
