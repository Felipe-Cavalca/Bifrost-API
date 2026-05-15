<?php

namespace Bifrost\DataTypes;

use Bifrost\Enum\Field;
use Bifrost\Include\AbstractFieldValue;
use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class Url implements Insertable, Responseable
{
    use AbstractFieldValue;

    public function __construct(mixed $url)
    {
        $this->init($url, Field::URL);
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
