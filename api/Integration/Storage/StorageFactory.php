<?php

declare(strict_types=1);

namespace Bifrost\Integration\Storage;

use Bifrost\Core\Settings;
use Bifrost\Interface\Storage as StorageInterface;

class StorageFactory
{
    public static function fromSettings(?Settings $settings = null): StorageInterface
    {
        $settings ??= new Settings();
        $driver = strtolower((string) ($settings->BFR_API_STORAGE_DRIVER ?? 'local'));

        return match ($driver) {
            's3' => S3Storage::fromSettings(settings: $settings),
            default => LocalStorage::fromSettings(settings: $settings),
        };
    }
}
