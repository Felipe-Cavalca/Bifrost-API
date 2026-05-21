<?php

namespace Bifrost\Integration;

class Apcu
{
    private static array $fallback = [];
    private static ?bool $enabled = null;
    private static string|false|null $enabledEnv = null;
    private static ?int $ttl = null;
    private static string|false|null $ttlEnv = null;

    public static function store(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!self::enabled()) {
            return false;
        }

        $ttl = $ttl ?? self::defaultTtl();

        if (function_exists('apcu_store')) {
            $stored = apcu_store($key, $value, $ttl);
            if ($stored) {
                return true;
            }
        }

        self::$fallback[$key] = $value;
        return true;
    }

    public static function fetch(string $key): mixed
    {
        if (!self::enabled()) {
            return null;
        }

        if (function_exists('apcu_fetch')) {
            $success = false;
            $value = apcu_fetch($key, $success);
            if ($success) {
                return $value;
            }
        }

        return self::$fallback[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        if (!self::enabled()) {
            return false;
        }

        if (function_exists('apcu_exists')) {
            if (apcu_exists($key)) {
                return true;
            }
        }

        return array_key_exists($key, self::$fallback);
    }

    public static function delete(string $key): bool
    {
        if (!self::enabled()) {
            return false;
        }

        if (function_exists('apcu_delete')) {
            if (apcu_delete($key)) {
                return true;
            }
        }

        if (!array_key_exists($key, self::$fallback)) {
            return false;
        }

        unset(self::$fallback[$key]);
        return true;
    }

    private static function enabled(): bool
    {
        $enabled = getenv('BFR_API_CACHE_APCU_ENABLED');

        if (self::$enabled !== null && self::$enabledEnv === $enabled) {
            return self::$enabled;
        }

        self::$enabledEnv = $enabled;

        if ($enabled === false || $enabled === '') {
            return self::$enabled = true;
        }

        return self::$enabled = filter_var($enabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private static function defaultTtl(): int
    {
        $ttlEnv = getenv('BFR_API_CACHE_APCU_TTL');

        if (self::$ttl !== null && self::$ttlEnv === $ttlEnv) {
            return self::$ttl;
        }

        self::$ttlEnv = $ttlEnv;
        $ttl = (int) ($ttlEnv ?: 3600);

        return self::$ttl = $ttl > 0 ? $ttl : 3600;
    }
}
