<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Filesystem;

use Bifrost\DataTypes\AbstractDataType;

final readonly class FolderName extends AbstractDataType
{
    /**
     * Verifica se o valor e um nome de pasta valido.
     */
    public static function isValid(mixed $value): bool
    {
        return is_string($value)
            && $value !== ''
            && preg_match('/^(?!.*\.)[^\\/:*?"<>|]{1,255}$/', $value) === 1;
    }
}
