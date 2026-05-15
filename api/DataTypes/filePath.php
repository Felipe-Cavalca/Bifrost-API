<?php

namespace Bifrost\DataTypes;

use Bifrost\Enum\Field;
use Bifrost\Include\AbstractFieldValue;
use Bifrost\Interface\Insertable;
use Bifrost\Interface\Responseable;

class FilePath implements Insertable, Responseable
{
    use AbstractFieldValue;

    public function __construct(mixed $filePath)
    {
        $this->init($filePath, Field::FILE_PATH);
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
