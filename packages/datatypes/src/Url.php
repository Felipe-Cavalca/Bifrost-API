<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

final readonly class Url extends AbstractDataType
{
    /**
     * Verifica se o valor e uma URL valida.
     */
    public static function isValid(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
