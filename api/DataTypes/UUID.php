<?php

namespace Bifrost\DataTypes;

use Bifrost\Enum\Field;
use Bifrost\Include\AbstractFieldValue;
use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class UUID implements Insertable, Responseable
{
    use AbstractFieldValue;

    public function __construct(mixed $uuid)
    {
        $this->init($uuid, Field::UUID);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value();
    }
}
