<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

final readonly class DateTime extends AbstractDataType
{
    public static function now(): self
    {
        return self::from('now');
    }

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
