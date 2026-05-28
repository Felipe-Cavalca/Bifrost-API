<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\HttpAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Response implements HttpAttribute
{
    public function __construct(private readonly array $schema)
    {
    }

    public function options(): array
    {
        return ['response' => $this->schema];
    }
}
