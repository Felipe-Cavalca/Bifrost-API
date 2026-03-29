<?php

namespace Bifrost\Integration;

use Bifrost\Core\Settings;

class Apcu
{
    private static array $fallback = [];

    public static function store(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!self::enabled()) {
            return false;
        }

        $ttl = $ttl ?? self::defaultTtl();

        if (function_exists('apcu_store')) {
            return apcu_store($key, $value, $ttl);
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
            return $success ? $value : null;
        }

        return self::$fallback[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        if (!self::enabled()) {
            return false;
        }

        if (function_exists('apcu_exists')) {
            return apcu_exists($key);
        }

        return array_key_exists($key, self::$fallback);
    }

    public static function delete(string $key): bool
    {
        if (!self::enabled()) {
            return false;
        }

        if (function_exists('apcu_delete')) {
            return (bool) apcu_delete($key);
        }

        if (!array_key_exists($key, self::$fallback)) {
            return false;
        }

        unset(self::$fallback[$key]);
        return true;
    }

    private static function enabled(): bool
    {
        $settings = new Settings();
        $enabled = $settings->BFR_API_APCU_ENABLED;

        if ($enabled === null || $enabled === '') {
            return true;
        }

        return filter_var($enabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private static function defaultTtl(): int
    {
        $settings = new Settings();
        $ttl = (int) ($settings->BFR_API_APCU_TTL ?? 3600);

        return $ttl > 0 ? $ttl : 3600;
    }
}
