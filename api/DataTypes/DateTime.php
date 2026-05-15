<?php

declare(strict_types=1);

namespace Bifrost\DataTypes;

use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class DateTime implements Insertable, Responseable
{
    private readonly \DateTimeImmutable $dateTime;

    public function __construct(string $dateTime = 'now')
    {
        if (trim($dateTime) === '') {
            throw new \InvalidArgumentException('Invalid date time.');
        }

        try {
            $this->dateTime = new \DateTimeImmutable($dateTime);
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException('Invalid date time.', 0, $exception);
        }
    }

    public static function now(): self
    {
        return new self(dateTime: 'now');
    }

    public function assertFuture(): void
    {
        if ($this->dateTime <= new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Date time must be in the future.');
        }
    }

    public function value(): string
    {
        return $this->dateTime->format('Y-m-d H:i:s');
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
