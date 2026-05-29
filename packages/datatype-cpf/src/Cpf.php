<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Brazil;

use Bifrost\DataTypes\AbstractDataType;

final readonly class Cpf extends AbstractDataType
{
    /**
     * Verifica se o valor e um CPF valido.
     */
    public static function isValid(mixed $value): bool
    {
        $digits = self::digits($value);
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }

            $checkDigit = ((10 * $sum) % 11) % 10;
            if ((int) $digits[$t] !== $checkDigit) {
                return false;
            }
        }

        return true;
    }

    protected static function normalize(mixed $value): string
    {
        return self::digits($value);
    }

    private static function digits(mixed $value): string
    {
        return preg_replace('/\D/', '', (string) $value) ?? '';
    }
}
