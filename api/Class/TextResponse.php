<?php

namespace Bifrost\Class;

use Bifrost\Interface\Responseable;

class TextResponse implements Responseable
{
    public function __construct(
        private readonly string $text
    ) {
    }

    public function jsonSerialize(): string
    {
        return $this->text;
    }
}
