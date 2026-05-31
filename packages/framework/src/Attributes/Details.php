<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\HttpAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Details implements HttpAttribute
{
    public function __construct(private readonly array $details)
    {
    }

    public function options(): array
    {
        return $this->details;
    }
}
