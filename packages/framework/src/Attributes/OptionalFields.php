<?php

declare(strict_types=1);

namespace Bifrost\Framework\Attributes;

use Attribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_METHOD)]
/**
 * Declara campos opcionais aceitos no corpo da request.
 *
 * Campos ausentes sao ignorados. Campos presentes sao validados pela regra informada.
 *
 * Exemplo: #[OptionalFields(['nickname' => 'string', 'age' => 'int'])]
 */
final class OptionalFields implements RequestValidatorAttribute
{
    /**
     * @param array<string, mixed> $fields Mapa de campo opcional para regra de validacao.
     */
    public function __construct(private readonly array $fields)
    {
    }

    /**
     * Valida apenas os campos opcionais presentes na request.
     *
     * @return Response|null Retorna null quando os campos presentes sao validos, ou resposta 400.
     */
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

    /**
     * @return array{optionalFields: array<string, string>} Metadados dos campos opcionais.
     */
    public function options(): array
    {
        $fields = [];
        foreach ($this->fields as $field => $rule) {
            $fields[(string) $field] = ValidationRule::describe($rule);
        }

        return ['optionalFields' => $fields];
    }
}
