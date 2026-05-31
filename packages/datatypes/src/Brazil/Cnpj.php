<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Brazil;

use Bifrost\DataTypes\AbstractDataType;

final readonly class Cnpj extends AbstractDataType
{
    /**
     * Verifica se o valor e um CNPJ valido.
     */
    public static function isValid(mixed $value): bool
    {
        $digits = self::digits($value);
        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }

        return self::digit($digits, 12) === (int) $digits[12]
            && self::digit($digits, 13) === (int) $digits[13];
    }

    protected static function normalize(mixed $value): string
    {
        return self::digits($value);
    }

    private static function digit(string $digits, int $position): int
    {
        $weights = $position === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += (int) $digits[$index] * $weight;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }

    private static function digits(mixed $value): string
    {
        return preg_replace('/\D/', '', (string) $value) ?? '';
    }
}
