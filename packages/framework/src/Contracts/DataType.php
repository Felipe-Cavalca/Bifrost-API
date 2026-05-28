<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

interface DataType
{
    public static function from(mixed $value): static;

    public static function isValid(mixed $value): bool;

    public function value(): mixed;
}
