<?php

/**
 * It is responsible for managing the cache.
 *
 * @category Core
 * @package Bifrost\Core
 */

namespace Bifrost\Integration\Cache;

use Redis;
use Bifrost\Core\Settings;
use Bifrost\Interface\Cache as CacheInterface;

/**
 * It is responsible for managing the cache.
 *
 * @package Bifrost\Core
 */
class RedisCache implements CacheInterface
{
    /** It is responsible for controlling the initialization of the cache. */
    private static $redis;
    /** Indicates whether Redis is enabled. */
    private static bool $enabled = true;

    /**
     * It is responsible for connecting to the cache.
     */
    private static function conn(): void
    {
        $settings = new Settings();
        $host = $settings->BFR_API_REDIS_HOST;
        $port = $settings->BFR_API_REDIS_PORT;

        if (empty($host) || empty($port)) {
            self::$enabled = false;
            self::$redis = null;
            return;
        }

        try {
            self::$redis = new Redis();
            $connected = @self::$redis->connect($host, $port);
            if (!$connected) {
                self::$enabled = false;
                self::$redis = null;
            }
        } catch (\Throwable $e) {
            self::$enabled = false;
            self::$redis = null;
        }
    }

    private static function ensureConnection(): void
    {
        if (!self::$enabled) {
            return;
        }

        if (empty(self::$redis)) {
            self::conn();
        }
    }

    /**
     * It is responsible for setting the cache.
     */
    public static function set(string $key, mixed $value, int $expire = 1): bool
    {
        self::ensureConnection();

        if (!self::$enabled) {
            return false;
        }

        if (is_callable($value)) {
            $value = $value();
        }

        return self::$redis->set($key, serialize($value), $expire);
    }

    /**
     * It is responsible for getting the cache.
     */
    public static function get(string $key, mixed $value = null, int $expire = 1): mixed
    {
        self::ensureConnection();

        if (!self::$enabled) {
            if (is_callable($value)) {
                return $value();
            }
            return $value;
        }

        if (!self::exists($key) && !empty($value)) {
            self::set($key, $value, $expire);
        }

        return unserialize(self::$redis->get($key));
    }

    /**
     * It is responsible for checking if the cache exists.
     */
    public static function exists(string $key): bool
    {
        self::ensureConnection();

        if (!self::$enabled) {
            return false;
        }

        return (bool) self::$redis->exists($key);
    }

    /**
     * It is responsible for deleting the cache.
     */
    public static function del(string $key): bool
    {
        self::ensureConnection();

        if (!self::$enabled) {
            return false;
        }

        return (bool) self::$redis->del($key);
    }
}
