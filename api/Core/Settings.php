<?php

/**
 * It is responsible for managing the system settings.
 *
 * @category Core
 * @copyright 2024
 */

namespace Bifrost\Core;

use Bifrost\Core\AppError;
use Bifrost\Class\HttpResponse;

/**
 * It is responsible for managing the system settings.
 *
 * @package Bifrost\Core
 * @author Felipe dos S. Cavalca
 */
final class Settings
{
    /** It is responsible for controlling the initialization of the settings. */
    private static bool $initialized = false;
    private static array $requestEnvCache = [];

    /**
     * It is responsible for initializing the settings.
     *
     * @uses Settings::iniSet()
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * It is responsible for returning the value of the requested property.
     *
     * @param string $name The name of the property to be returned.
     * @uses Settings::getSettingsDatabase()
     * @uses Settings::getEnv()
     * @return mixed
     */
    public function __get($name): mixed
    {
        switch ($name) {
            default:
                return $this->getEnv($name);
        }
    }

    /**
     * It is responsible for returning the value of the requested property of the environment.
     *
     * @param string $param The name of the property to be returned.
     * @param bool $required Indicates whether the property is required.
     * @uses HttpError::__construct()
     * @return mixed
     */
    protected static function getEnv(string $param, bool $required = false): mixed
    {
        $cacheKey = $param . ':' . ($required ? '1' : '0');
        if (array_key_exists($cacheKey, self::$requestEnvCache)) {
            return self::$requestEnvCache[$cacheKey];
        }

        $apcuKey = "settings:env:{$cacheKey}";
        if (self::settingsApcuEnabled() && function_exists('apcu_fetch')) {
            $success = false;
            $cached = apcu_fetch($apcuKey, $success);
            if ($success) {
                self::$requestEnvCache[$cacheKey] = $cached;
                return $cached;
            }
        }

        $value = getenv($param);

        if ($required && ($value === false || $value === '')) {
            throw new AppError(HttpResponse::internalServerError(
                message: "The environment variable '{$param}' is required.",
                errors: ["{$param}" => "The environment variable '{$param}' is required."]
            ));
        }

        $parsedValue = $value === false ? null : $value;
        self::$requestEnvCache[$cacheKey] = $parsedValue;
        if (self::settingsApcuEnabled() && function_exists('apcu_store')) {
            apcu_store($apcuKey, $parsedValue, self::settingsApcuTtl());
        }

        return $parsedValue;
    }

    /**
     * It is responsible for returning the first non-empty environment variable value.
     *
     * @param array $params The environment variable aliases.
     * @param bool $required Indicates whether one alias is required.
     * @return mixed
     */
    protected static function getEnvFromAliases(array $params, bool $required = false): mixed
    {
        foreach ($params as $param) {
            $value = static::getEnv($param);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        if ($required && !empty($params)) {
            return static::getEnv($params[0], true);
        }

        return null;
    }

    /**
     * Parse env booleans in a safer way.
     *
     * @param mixed $value The env value.
     * @param bool $default Default value when parsing fails.
     * @return bool
     */
    private static function parseEnvBool(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * It is responsible for enabling APCu settings cache (non-CLI only).
     *
     * @return bool
     */
    private static function settingsApcuEnabled(): bool
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $enabledRaw = getenv('BFR_API_SETTINGS_APCU_ENABLED');
        if ($enabledRaw === false || $enabledRaw === '') {
            $enabledRaw = getenv('BFR_API_CACHE_APCU_ENABLED');
        }
        if ($enabledRaw === false || $enabledRaw === '') {
            $enabledRaw = getenv('BFR_API_APCU_ENABLED');
        }
        $enabled = static::parseEnvBool($enabledRaw === false ? null : $enabledRaw, false);

        return $enabled && function_exists('apcu_enabled') ? apcu_enabled() : $enabled;
    }

    /**
     * It is responsible for returning APCu settings cache TTL.
     *
     * @return int
     */
    private static function settingsApcuTtl(): int
    {
        $ttlRaw = getenv('BFR_API_SETTINGS_APCU_TTL');
        if ($ttlRaw === false || $ttlRaw === '') {
            $ttlRaw = getenv('BFR_API_CACHE_APCU_TTL');
        }
        if ($ttlRaw === false || $ttlRaw === '') {
            $ttlRaw = getenv('BFR_API_APCU_TTL');
        }
        $ttl = (int) ($ttlRaw === false ? 60 : $ttlRaw);

        return $ttl > 0 ? $ttl : 60;
    }

    /**
     * It is responsible for setting the headers of the response.
     *
     * @return void
     */
    private static function setHeaders(): void
    {
        header("X-Powered-By: PHP/" . phpversion());
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Expose-Headers: Authorization");
    }

    /**
     * It is responsible for setting the PHP configuration.
     *
     * @uses Settings::getEnv()
     * @return void
     */
    private static function iniSet(): void
    {
        $displayErrors = static::parseEnvBool(
            static::getEnvFromAliases(['BFR_API_PHP_DISPLAY_ERRORS', 'BFR_API_DISPLAY_ERRORS']),
            false
        );
        ini_set("display_errors", $displayErrors ? '1' : '0');
        ini_set("display_startup_errors", $displayErrors ? '1' : '0');

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.save_handler', static::getEnv("BFR_API_SESSION_SAVE_HANDLER") ?? "files");
        ini_set('session.save_path', static::getEnv("BFR_API_SESSION_SAVE_PATH") ?? "/var/lib/php/sessions");
        ini_set('session.gc_maxlifetime', static::getEnv("BFR_API_SESSION_GC_MAXLIFETIME") ?? 1440);
        ini_set('session.cookie_lifetime', static::getEnv("BFR_API_SESSION_COOKIE_LIFETIME") ?? 0);
    }

    /**
     * It is responsible for initializing the settings.
     *
     * @uses Settings::iniSet()
     * @uses Settings::setHeaders()
     * @uses Settings::$initialized
     * @return void
     */
    public static function init(): void
    {
        // Valida se já foi inicializado
        if (self::$initialized) {
            return;
        }

        static::iniSet();
        static::setHeaders();

        self::$initialized = true;
    }

    /**
     * It is responsible for returning the database settings.
     *
     * @param string $databaseName The prefix name of the database in the .env file
     * @uses Settings::getEnv()
     * @return array
     */
    public function getSettingsDatabase(?string $databaseName = null): array
    {
        $prefix = "BFR_API_";

        if (!empty($databaseName)) {
            $databaseName = strtoupper($databaseName) . "_";
        }

        $driver = static::getEnvFromAliases([
            "{$prefix}{$databaseName}DB_DRIVER",
            "{$prefix}{$databaseName}SQL_DRIVER",
        ], true);
        $isNotSqlite = $driver !== "sqlite";

        return [
            "driver" => $driver,
            "host" => static::getEnvFromAliases([
                "{$prefix}{$databaseName}DB_HOST",
                "{$prefix}{$databaseName}SQL_HOST",
            ], $isNotSqlite),
            "port" => static::getEnvFromAliases([
                "{$prefix}{$databaseName}DB_PORT",
                "{$prefix}{$databaseName}SQL_PORT",
            ], $isNotSqlite),
            "database" => static::getEnvFromAliases([
                "{$prefix}{$databaseName}DB_NAME",
                "{$prefix}{$databaseName}SQL_DATABASE",
            ], true),
            "username" => static::getEnvFromAliases([
                "{$prefix}{$databaseName}DB_USER",
                "{$prefix}{$databaseName}SQL_USER",
            ], $isNotSqlite),
            "password" => static::getEnvFromAliases([
                "{$prefix}{$databaseName}DB_PASSWORD",
                "{$prefix}{$databaseName}SQL_PASSWORD",
            ], $isNotSqlite),
        ];
    }
}
