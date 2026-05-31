<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

final readonly class DateTime extends AbstractDataType
{
    /**
     * Cria um DateTime com o instante atual.
     */
    public static function now(): self
    {
        return self::from('now');
    }

    /**
     * Verifica se o valor pode ser convertido para data e hora.
     */
    public static function isValid(mixed $value): bool
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        try {
            new DateTimeImmutable($value);
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * Garante que a data e hora representada esta no futuro.
     */
    public function assertFuture(): void
    {
        if (new DateTimeImmutable((string) $this->value()) <= new DateTimeImmutable()) {
            throw new InvalidArgumentException('Data e hora devem estar no futuro.');
        }
    }

    protected static function normalize(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (new DateTimeImmutable((string) $value))->format('Y-m-d H:i:s');
    }
}
