<?php

declare(strict_types=1);

namespace Bifrost\Core;

class LoggerConfig
{
    public function __construct(
        private readonly string $driver,
        private readonly ?string $file,
        private readonly string $collection
    ) {
    }

    public static function fromSettings(?Settings $settings = null): self
    {
        $settings ??= new Settings();

        return new self(
            driver: strtolower((string) ($settings->BFR_API_LOG_DRIVER ?? 'error_log')),
            file: self::optionalString($settings->BFR_API_LOG_FILE),
            collection: self::optionalString($settings->BFR_API_LOG_COLLECTION) ?? 'logs'
        );
    }

    public function driver(): string
    {
        return $this->driver;
    }

    public function file(): ?string
    {
        return $this->file;
    }

    public function collection(): string
    {
        return $this->collection;
    }

    private static function optionalString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
