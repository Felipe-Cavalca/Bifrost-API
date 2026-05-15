<?php

namespace Bifrost\Core;

use Bifrost\DataTypes\UUID;
use Bifrost\Integration\Database\MongoDatabase;
use Bifrost\Interface\NoSqlDatabase;

final class Logger
{
    private static ?UUID $requestId = null;
    private static ?NoSqlDatabase $noSqlDatabase = null;

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
        $settings ??= new Settings();

        return strtolower((string) $settings->BFR_API_LOG_DRIVER) === 'none';
    }

    private static function write(string $level, string $message, array $context): void
    {
        $settings = new Settings();

        if (self::isDisabled(settings: $settings)) {
            return;
        }

        $config = LoggerConfig::fromSettings($settings);

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

        if ($config->driver() === 'mongo' && self::writeToMongo($entry, $settings, $config)) {
            return;
        }

        if ($config->driver() === 'file') {
            if ($config->file() === null) {
                error_log($encoded);
                return;
            }
            file_put_contents($config->file(), $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
            return;
        }

        error_log($encoded);
    }

    private static function writeToMongo(array $entry, Settings $settings, LoggerConfig $config): bool
    {
        try {
            $database = self::$noSqlDatabase ?? MongoDatabase::fromSettings($settings);
            $database->insertOne($config->collection(), $entry);

            return true;
        } catch (\Throwable) {
            return false;
        }
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
