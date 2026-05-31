<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_METHOD)]
final class RequiredParams implements RequestValidatorAttribute
{
    public function __construct(private readonly array $params)
    {
    }

    public function validate(Request $request): ?Response
    {
        $errors = [];

        foreach ($this->params as $param => $rule) {
            if (is_int($param)) {
                $param = (string) $rule;
                $rule = 'mixed';
            }

            $value = $request->query((string) $param);
            if ($value === null) {
                $errors[$param] = 'Parameter not found';
                continue;
            }

            if ($rule !== 'mixed' && !ValidationRule::validate($value, $rule)) {
                $errors[$param] = 'Invalid parameter type';
            }
        }

        if ($errors === []) {
            return null;
        }

        return Response::json(payload: ['message' => 'Invalid parameters', 'errors' => ['params' => $errors]], status: 400);
    }

    public function options(): array
    {
        $params = [];
        foreach ($this->params as $param => $rule) {
            if (is_int($param)) {
                $params[(string) $rule] = 'mixed';
                continue;
            }

            $params[(string) $param] = ValidationRule::describe($rule);
        }

        return ['params' => $params];
    }
}
