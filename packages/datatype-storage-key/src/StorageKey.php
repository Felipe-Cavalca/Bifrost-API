<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Storage;

use Bifrost\DataTypes\Filesystem\FilePath;

final readonly class StorageKey extends FilePath
{
    protected static function normalize(mixed $value): string
    {
        return str_replace('\\', '/', trim((string) $value));
    }

    /**
     * Verifica se o valor e uma chave relativa valida para storage.
     */
    public static function isValid(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $normalized = self::normalize($value);

        return !str_starts_with($normalized, '/') && parent::isValid($normalized);
    }
}
