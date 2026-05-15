<?php

declare(strict_types=1);

namespace Bifrost\Integration\Redis;

use Bifrost\Core\Settings;

class RedisConfig
{
    public function __construct(
        private readonly ?string $host,
        private readonly ?int $port,
        private readonly string $driver = 'redis',
        private readonly string $queueName = 'bifrost_queue'
    ) {
    }

    public static function forCache(?Settings $settings = null): self
    {
        $settings ??= new Settings();

        return new self(
            host: self::optionalString($settings->BFR_API_CACHE_REDIS_HOST),
            port: self::optionalInt($settings->BFR_API_CACHE_REDIS_PORT),
            driver: strtolower((string) ($settings->BFR_API_CACHE_DRIVER ?? 'redis'))
        );
    }

    public static function forQueue(?Settings $settings = null): self
    {
        $settings ??= new Settings();

        return new self(
            host: self::optionalString($settings->BFR_API_QUEUE_REDIS_HOST),
            port: self::optionalInt($settings->BFR_API_QUEUE_REDIS_PORT),
            queueName: self::optionalString($settings->BFR_API_QUEUE_NAME) ?? 'bifrost_queue'
        );
    }

    public function isRedisDriver(): bool
    {
        return $this->driver === 'redis';
    }

    public function isEnabled(): bool
    {
        return $this->isRedisDriver() && $this->host !== null && $this->port !== null;
    }

    public function host(): ?string
    {
        return $this->host;
    }

    public function port(): ?int
    {
        return $this->port;
    }

    public function queueName(): string
    {
        return $this->queueName;
    }

    private static function optionalString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) ?: null;
    }
}
