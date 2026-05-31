<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_METHOD)]
final class OptionalFields implements RequestValidatorAttribute
{
    public function __construct(private readonly array $fields)
    {
    }

    public function validate(Request $request): ?Response
    {
        $errors = [];

        foreach ($this->fields as $field => $rule) {
            if (is_int($field)) {
                continue;
            }

            $value = $request->input((string) $field);
            if ($value === null) {
                continue;
            }

            if (!ValidationRule::validate($value, $rule)) {
                $errors[$field] = 'Invalid field type';
            }
        }

        if ($errors === []) {
            return null;
        }

        return Response::json(payload: ['message' => 'Invalid optional fields', 'errors' => ['fields' => $errors]], status: 400);
    }

    public function options(): array
    {
        $fields = [];
        foreach ($this->fields as $field => $rule) {
            $fields[(string) $field] = ValidationRule::describe($rule);
        }

        return ['optionalFields' => $fields];
    }
}
