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
 * Declara parametros obrigatorios na query string.
 *
 * Aceita uma lista simples de nomes ou um mapa nome => regra.
 *
 * Exemplo: #[RequiredParams(['page' => 'int-string', 'filter'])]
 */
final class RequiredParams implements RequestValidatorAttribute
{
    /**
     * @param array<int|string, mixed> $params Parametros obrigatorios. Use ['param'] para aceitar qualquer valor
     *                                        ou ['param' => 'string'] para validar tipo/regra.
     */
    public function __construct(private readonly array $params)
    {
    }

    /**
     * Valida os parametros obrigatorios da query string.
     *
     * @return Response|null Retorna null quando os parametros sao validos, ou resposta 400.
     */
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

    /**
     * @return array{params: array<string, string>} Metadados dos parametros obrigatorios.
     */
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
