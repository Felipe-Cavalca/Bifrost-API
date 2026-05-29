<?php

declare(strict_types=1);

namespace Bifrost\Framework\Validation;

use Bifrost\Framework\Contracts\DataType;

/**
 * Valida valores usados pelos attributes de request.
 *
 * Aceita regras nomeadas, DataTypes ou callables.
 */
final class ValidationRule
{
    /**
     * Verifica se um valor atende a uma regra.
     *
     * Regras nomeadas suportadas: int, int-string, string, float, numeric, bool,
     * array, object, null, email, url, base64, json e uuid.
     *
     * @param mixed $rule Nome da regra, classe DataType, instancia DataType ou callable.
     */
    public static function validate(mixed $value, mixed $rule): bool
    {
        if (is_string($rule) && enum_exists($rule) === false && class_exists($rule) === false) {
            return self::validateNamedRule($value, strtolower($rule));
        }

        if (is_string($rule) && is_subclass_of($rule, DataType::class)) {
            return $rule::isValid($value);
        }

        if ($rule instanceof DataType) {
            return $rule::isValid($value);
        }

        if (is_callable($rule)) {
            return (bool) $rule($value);
        }

        return false;
    }

    /**
     * Descreve uma regra em formato legivel para metadados de endpoint.
     */
    public static function describe(mixed $rule): string
    {
        if (is_string($rule)) {
            return $rule;
        }

        if (is_object($rule)) {
            return $rule::class;
        }

        return get_debug_type($rule);
    }

    private static function validateNamedRule(mixed $value, string $rule): bool
    {
        return match ($rule) {
            'int', 'integer' => is_int($value),
            'int-string', 'integer-string' => is_string($value) && ctype_digit($value),
            'string' => is_string($value),
            'float' => is_float($value),
            'numeric' => is_numeric($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'null' => $value === null,
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'base64' => is_string($value) && base64_decode($value, true) !== false,
            'json' => is_string($value) && json_decode($value) !== null,
            'uuid' => is_string($value)
                && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1,
            default => false,
        };
    }
}
