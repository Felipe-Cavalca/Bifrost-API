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
        $value = getenv($param);

        if ($required && ($value === false || $value === '')) {
            throw new AppError(HttpResponse::internalServerError(
                message: "The environment variable '{$param}' is required.",
                errors: ["{$param}" => "The environment variable '{$param}' is required."]
            ));
        }

        return $value === false ? null : $value;
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
        $displayErrors = static::getEnv("BFR_API_DEBUG_SHOW_ERRORS") ?? false;
        ini_set("display_errors", (bool) $displayErrors);
        ini_set("display_startup_errors", (bool) $displayErrors);
        static::configureApcu();

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.save_handler', static::getEnv("BFR_API_SESSION_HANDLER") ?? "files");
        ini_set('session.save_path', static::getEnv("BFR_API_SESSION_PATH") ?? "/var/lib/php/sessions");
        ini_set('session.gc_maxlifetime', static::getEnv("BFR_API_SESSION_TTL") ?? 1440);
        ini_set('session.cookie_lifetime', static::getEnv("BFR_API_SESSION_COOKIE_TTL") ?? 0);
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
        $prefix = "BFR_API_DB_";

        if (!empty($databaseName)) {
            $databaseName = strtoupper($databaseName) . "_";
        }

        $driver = static::getEnv("{$prefix}{$databaseName}DRIVER", true);
        $isNotSqlite = $driver !== "sqlite";

        return [
            "driver" => $driver,
            "host" => static::getEnv("{$prefix}{$databaseName}HOST", $isNotSqlite),
            "port" => static::getEnv("{$prefix}{$databaseName}PORT", $isNotSqlite),
            "database" => static::getEnv("{$prefix}{$databaseName}NAME", true),
            "username" => static::getEnv("{$prefix}{$databaseName}USER", $isNotSqlite),
            "password" => static::getEnv("{$prefix}{$databaseName}PASSWORD", $isNotSqlite),
        ];
    }

    /**
     * Enables APCu when configured so runtime caches (including composer) benefit from it.
     *
     * @return void
     */
    private static function configureApcu(): void
    {
        $apcuEnabled = static::getEnv('BFR_API_CACHE_APCU_ENABLED');

        if ($apcuEnabled === null || $apcuEnabled === '') {
            return;
        }

        $enabled = filter_var($apcuEnabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($enabled === false) {
            return;
        }

        // These directives are CLI-safe and help when running tests or workers.
        @ini_set('apc.enable_cli', '1');
    }
}
