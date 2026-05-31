<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Filesystem;

use Bifrost\DataTypes\AbstractDataType;

final readonly class FolderPath extends AbstractDataType
{
    /**
     * Verifica se o valor e um caminho de pasta valido.
     */
    public static function isValid(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        foreach (explode('/', $value) as $index => $folder) {
            if ($index === 0 && $folder === '') {
                continue;
            }

            if (!FolderName::isValid($folder)) {
                return false;
            }
        }

        return true;
    }
}
