<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

final readonly class Email extends AbstractDataType
{
    /**
     * Verifica se o valor e um e-mail valido.
     */
    public static function isValid(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected static function normalize(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}
