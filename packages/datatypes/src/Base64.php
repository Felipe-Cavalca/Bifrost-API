<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

final readonly class Base64 extends AbstractDataType
{
    public static function isValid(mixed $value): bool
    {
        return is_string($value) && base64_decode($value, true) !== false;
    }
}
