<?php

namespace Bifrost\Core;

use Bifrost\DataTypes\UUID;
use Bifrost\Integration\Database\MongoDatabase;
use Bifrost\Interface\NoSqlDatabase;

final class Logger
{
    private static ?UUID $requestId = null;
    private static ?NoSqlDatabase $noSqlDatabase = null;
    private static ?string $driver = null;
    private static string|false|null $driverEnv = null;

    public static function debug(string $message, array $context = []): void
    {
        self::write('debug', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function exception(\Throwable $exception, array $context = []): void
    {
        self::error('Unhandled exception', array_merge($context, [
            'exception' => [
                'class' => $exception::class,
                'code' => $exception->getCode(),
            ],
        ]));
    }

    public static function requestId(): UUID
    {
        if (self::$requestId === null) {
            self::$requestId = self::resolveRequestId();
        }

        return self::$requestId;
    }

    public static function resetRequestId(?UUID $requestId = null): void
    {
        self::$requestId = $requestId;
        self::$driver = null;
        self::$driverEnv = null;
    }

    public static function setNoSqlDatabase(?NoSqlDatabase $noSqlDatabase): void
    {
        self::$noSqlDatabase = $noSqlDatabase;
    }

    public static function sendRequestIdHeader(): void
    {
        if (!headers_sent() && !self::isDisabled()) {
            header('X-Request-Id: ' . self::requestId()->value());
        }
    }

    public static function isDisabled(?Settings $settings = null): bool
    {
        return !self::isEnabled($settings);
    }

    public static function isEnabled(?Settings $settings = null): bool
    {
        return self::driver($settings) !== 'none';
    }

    private static function write(string $level, string $message, array $context): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $settings = new Settings();
        $driver = self::driver($settings);

        $entry = [
            'timestamp' => gmdate('c'),
            'level' => $level,
            'message' => $message,
            'request_id' => self::requestId()->value(),
            'context' => $context,
        ];
        $encoded = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (!is_string($encoded)) {
            return;
        }

        if ($driver === 'mongo' && self::writeToMongo($entry, $settings)) {
            return;
        }

        if ($driver === 'file') {
            $logFile = self::optionalString($settings->BFR_API_LOG_FILE);
            if ($logFile === null) {
                error_log($encoded);
                return;
            }
            file_put_contents($logFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
            return;
        }

        error_log($encoded);
    }

    private static function writeToMongo(array $entry, Settings $settings): bool
    {
        try {
            $database = self::$noSqlDatabase ?? MongoDatabase::fromSettings($settings);
            $database->insertOne(self::mongoCollection($settings), $entry);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private static function driver(?Settings $settings = null): string
    {
        $driver = $settings !== null
            ? (string) ($settings->BFR_API_LOG_DRIVER ?? 'error_log')
            : (getenv('BFR_API_LOG_DRIVER') ?: 'error_log');

        if (self::$driver !== null && self::$driverEnv === $driver) {
            return self::$driver;
        }

        self::$driverEnv = $driver;

        return self::$driver = strtolower((string) $driver);
    }

    private static function mongoCollection(Settings $settings): string
    {
        return self::optionalString($settings->BFR_API_LOG_COLLECTION) ?? 'logs';
    }

    private static function optionalString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function resolveRequestId(): UUID
    {
        $header = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;

        if (is_string($header)) {
            try {
                return new UUID($header);
            } catch (AppError) {
            }
        }

        return new UUID(UUID::generate());
    }
}
