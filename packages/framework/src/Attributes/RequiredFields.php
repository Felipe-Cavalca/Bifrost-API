<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_METHOD)]
final class RequiredFields implements RequestValidatorAttribute
{
    public function __construct(private readonly array $fields)
    {
    }

    public function validate(Request $request): ?Response
    {
        $errors = [];

        foreach ($this->fields as $field => $rule) {
            if (is_int($field)) {
                $field = (string) $rule;
                $rule = 'mixed';
            }

            $value = $request->input((string) $field);
            if ($value === null) {
                $errors[$field] = 'Field not found';
                continue;
            }

            if ($rule !== 'mixed' && !ValidationRule::validate($value, $rule)) {
                $errors[$field] = 'Invalid field type';
            }
        }

        if ($errors === []) {
            return null;
        }

        return Response::json(payload: ['message' => 'Invalid fields', 'errors' => ['fields' => $errors]], status: 400);
    }

    public function options(): array
    {
        return ['fields' => $this->describeFields()];
    }

    private function describeFields(): array
    {
        $fields = [];
        foreach ($this->fields as $field => $rule) {
            if (is_int($field)) {
                $fields[(string) $rule] = 'mixed';
                continue;
            }

            $fields[(string) $field] = ValidationRule::describe($rule);
        }

        return $fields;
    }
}
