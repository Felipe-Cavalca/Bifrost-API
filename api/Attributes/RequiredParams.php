<?php

namespace Bifrost\Attributes;

use Attribute;
use Bifrost\Class\HttpResponse;
use Bifrost\Interface\Attribute as AttributeInterface;
use Bifrost\Core\Get;
use Bifrost\Enum\Field;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Responseable;

#[Attribute]
class RequiredParams implements AttributeInterface, AttributeBefore
{

    public static array $params = [];
    private Get $Get;
    private array $errors = [];

    public function __construct(...$params)
    {
        self::$params = $params[0];
        $this->Get = new Get();
    }

    /**
     * Valida os parâmetros obrigatórios e retorna erro caso algum parâmetro não seja válido.
     * @return null|Responseable Erro de requisição ou null caso não tenha erro.
     */
    public function before(): null|Responseable
    {
        if (!$this->validateRequiredParams(self::$params)) {
            return HttpResponse::badRequest(
                errors: $this->getErrors(),
                message: "Invalid parameters"
            );
        }
        return null;
    }

    /**
     * Retorna os parâmetros obrigatórios para o endpoint.
     * @return array Parâmetros obrigatórios para o endpoint.
     */
    public function getOptions(): array
    {
        $params = [];
        foreach (self::$params as $field => $filter) {
            $params[$field] = $filter->value ?? null;
        }
        return ["params" => $params];
    }

    /**
     * Valida os parâmetros obrigatórios e retorna erro caso algum parâmetro não seja válido.
     * @param array $params Array contendo os parâmetros obrigatórios para o endpoint.
     * @return bool True caso todos os parâmetros sejam válidos, false caso contrário.
     */
    private function validateRequiredParams(array $params): bool
    {
        $this->errors = [];

        foreach ($params as $field => $filter) {
            if (is_int($field)) {
                $field = $filter;
                $filter = Field::DEFAULT;
            }

            if (!static::existParam($field)) {
                $this->errors[$field] = "Field not found";
            }

            if (empty($this->errors[$field]) && !static::validateType($field, $filter)) {
                $this->errors[$field] = "Invalid parameter type";
            }
        }

        if (empty($this->errors)) {
            return true;
        }
        return false;
    }

    /**
     * Retorna os erros encontrados na validação dos parâmetros obrigatórios.
     * @return array Retorna os erros encontrados na validação dos parâmetros obrigatórios.
     */
    private function getErrors(): array
    {
        return ["params" => $this->errors];
    }

    /**
     * Valida se o parâmetro existe no GET.
     * @param string $field Parâmetro a ser verificado se existe no GET.
     * @return bool Retorna true se o parâmetro existir no GET, false caso contrário.
     */
    private function existParam(string $field): bool
    {
        return isset($this->Get->$field);
    }

    /**
     * Valida o tipo do parâmetro.
     * @param string $field Parâmetro cujo tipo será validado.
     * @param Field $filter Tipo do parâmetro a ser validado.
     * @return bool Retorna true se o tipo do parâmetro for válido, false caso contrário.
     */
    private function validateType(string $field, Field $filter): bool
    {
        return $filter->validate($this->Get->$field);
    }
}
