<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

final readonly class Json extends AbstractDataType
{
    /**
     * Verifica se o valor e uma string JSON valida.
     */
    public static function isValid(mixed $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
