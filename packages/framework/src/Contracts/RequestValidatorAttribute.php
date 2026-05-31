<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

interface RequestValidatorAttribute extends HttpAttribute
{
    public function validate(Request $request): ?Response;
}
