<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

#[Attribute(Attribute::TARGET_METHOD)]
final class Method implements RequestValidatorAttribute
{
    /** @var list<string> */
    private array $methods;

    public function __construct(string ...$methods)
    {
        $this->methods = array_map('strtoupper', $methods);
    }

    public function validate(Request $request): ?Response
    {
        if (in_array($request->method(), $this->methods, true)) {
            return null;
        }

        return Response::json(
            payload: ['message' => sprintf('Method %s is not allowed for this endpoint.', $request->method())],
            status: 405,
            headers: ['Allow' => implode(', ', $this->methods)]
        );
    }

    public function options(): array
    {
        return ['methods' => $this->methods];
    }
}
