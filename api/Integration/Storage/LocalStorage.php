<?php

declare(strict_types=1);

namespace Bifrost\Integration\Storage;

use Bifrost\Core\Settings;
use Bifrost\DataTypes\DateTime;
use Bifrost\DataTypes\StorageKey;
use Bifrost\Interface\Storage as StorageInterface;

class LocalStorage implements StorageInterface
{
    public function __construct(private readonly string $rootPath) {}

    public static function fromSettings(?Settings $settings = null): self
    {
        $settings ??= new Settings();
        $path = $settings->BFR_API_STORAGE_LOCAL_PATH ?? __DIR__ . '/../../tmp/storage';

        return new self(rootPath: (string) $path);
    }

    public function put(StorageKey $key, string $body, array $options = []): array
    {
        $path = $this->pathFor($key);
        $directory = dirname($path);

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new \RuntimeException('Unable to create storage directory.');
        }

        if (file_put_contents($path, $body) === false) {
            throw new \RuntimeException('Unable to write storage file.');
        }

        return [
            'Key' => $key->value(),
            'ContentLength' => strlen($body),
        ];
    }

    public function get(StorageKey $key, array $options = []): array
    {
        $path = $this->pathFor($key);

        if (!is_file($path)) {
            throw new \RuntimeException('Storage file not found.');
        }

        return [
            'Key' => $key->value(),
            'Body' => file_get_contents($path),
            'ContentLength' => filesize($path),
        ];
    }

    public function delete(StorageKey $key, array $options = []): array
    {
        $path = $this->pathFor($key);
        $deleted = !is_file($path) || unlink($path);

        return [
            'Key' => $key->value(),
            'Deleted' => $deleted,
        ];
    }

    public function createPresignedUrl(
        StorageKey $key,
        DateTime $expires = new DateTime('+15 minutes'),
        array $options = []
    ): string {
        return 'file://' . $this->pathFor($key);
    }

    public function getClient(): mixed
    {
        return null;
    }

    private function pathFor(StorageKey $key): string
    {
        return rtrim($this->rootPath, '\\/')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $key->value());
    }
}
