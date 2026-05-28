<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Filesystem;

use Bifrost\DataTypes\AbstractDataType;

readonly class FilePath extends AbstractDataType
{
    public static function isValid(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        $parts = explode('/', $value);
        foreach (array_slice($parts, 0, -1) as $folder) {
            if (!FolderName::isValid($folder)) {
                return false;
            }
        }

        return FileName::isValid((string) end($parts));
    }
}
