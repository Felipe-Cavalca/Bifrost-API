<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use Bifrost\Framework\Contracts\DataType;
use InvalidArgumentException;
use JsonSerializable;

abstract readonly class AbstractDataType implements DataType, JsonSerializable
{
    final protected function __construct(protected mixed $value)
    {
    }

    public static function from(mixed $value): static
    {
        if (!static::isValid($value)) {
            throw new InvalidArgumentException(sprintf('Valor invalido para %s.', static::class));
        }

        return new static(static::normalize($value));
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->value();
    }

    public function __toString(): string
    {
        return (string) $this->value();
    }

    protected static function normalize(mixed $value): mixed
    {
        return $value;
    }
}
