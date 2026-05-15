<?php

namespace Bifrost\DataTypes;

use Bifrost\Enum\Field;
use Bifrost\Include\AbstractFieldValue;
use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class Base64 implements Insertable, Responseable
{
    use AbstractFieldValue;

    public function __construct(mixed $base64)
    {
        $this->init($base64, Field::BASE64);
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
