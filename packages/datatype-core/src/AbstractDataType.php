<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use Bifrost\Framework\Contracts\DataType;
use Bifrost\Framework\Contracts\Insertable;
use InvalidArgumentException;
use JsonSerializable;

abstract readonly class AbstractDataType implements DataType, Insertable, JsonSerializable
{
    final protected function __construct(protected mixed $value)
    {
    }

    /**
     * Cria e valida uma instancia do DataType.
     */
    public static function from(mixed $value): static
    {
        if (!static::isValid($value)) {
            throw new InvalidArgumentException(sprintf('Valor invalido para %s.', static::class));
        }

        return new static(static::normalize($value));
    }

    /**
     * Retorna o valor normalizado.
     */
    public function value(): string|int|bool|float|null
    {
        return $this->value;
    }

    /**
     * Retorna o valor serializavel.
     */
    public function jsonSerialize(): string|int|bool|float|null
    {
        return $this->value();
    }

    /**
     * Retorna a representacao textual do valor.
     */
    public function __toString(): string
    {
        return (string) $this->value();
    }

    protected static function normalize(mixed $value): mixed
    {
        return $value;
    }
}
