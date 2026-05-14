<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class StorageKey implements Insertable, Responseable
{
    private readonly string $value;

    public function __construct(string $key)
    {
        $normalizedKey = str_replace('\\', '/', trim($key));

        if ($normalizedKey === '' || str_starts_with($normalizedKey, '/')) {
            throw new \InvalidArgumentException('Invalid storage key.');
        }

        foreach (explode('/', $normalizedKey) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, "\0")) {
                throw new \InvalidArgumentException('Invalid storage key.');
            }
        }

        $this->value = $normalizedKey;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
