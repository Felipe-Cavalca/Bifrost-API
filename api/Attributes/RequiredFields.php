<?php

namespace Bifrost\Attributes;

use Attribute;
use Bifrost\Class\HttpResponse;
use Bifrost\Core\Post;
use Bifrost\Enum\Field;
use Bifrost\Interface\Attribute as AttributeInterface;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Responseable;

#[Attribute]
class RequiredFields implements AttributeInterface, AttributeBefore
{

    public static array $fields = [];
    private Post $Post;
    private array $errors = [];

    public function __construct(...$fields)
    {
        self::$fields = $fields[0];
        $this->Post = new Post();
    }

    /**
     * Valida os campos obrigatórios e retorna erro caso algum campo não seja válido.
     * @return null|Responseable Retorna erro de requisição ou null caso todos os campos sejam válidos.
     */
    public function before(): null|Responseable
    {
        if (!$this->validateRequiredFields(self::$fields)) {
            return HttpResponse::badRequest(
                errors: $this->getErrors(),
                message: "Invalid fields"
            );
        }
        return null;
    }

    /**
     * Retorna os campos obrigatórios para o endpoint.
     * @return array Campos obrigatórios para o endpoint.
     */
    public function getOptions(): array
    {
        $campos = [];
        foreach (self::$fields as $field => $filter) {
            $campos[$field] = $filter->value ?? null;
        }
        return ["fields" => $campos];
    }

    /**
     * Valida os campos obrigatórios e retorna erro caso algum campo não seja válido.
     * @param array $fields Campos obrigatórios para o endpoint.
     * @return bool True caso todos os campos sejam válidos, false caso contrário.
     */
    public function validateRequiredFields(array $fields): bool
    {
        $this->errors = [];

        foreach ($fields as $field => $filter) {
            if (is_int($field)) {
                $field = $filter;
                $filter = Field::DEFAULT;
            }

            if (!static::existField($field)) {
                $this->errors[$field] = "Field not found";
            }

            if (empty($this->errors[$field]) && !static::validateType($field, $filter)) {
                $this->errors[$field] = "Invalid field type";
            }
        }

        if (empty($this->errors)) {
            return true;
        }
        return false;
    }

    /**
     * Retorna os erros encontrados na validação dos campos obrigatórios.
     * @return array Erros encontrados na validação dos campos obrigatórios.
     */
    private function getErrors(): array
    {
        return ["fields" => $this->errors];
    }

    /**
     * Valida se o campo existe no post.
     * @param string $field Campo a ser validado.
     * @return bool True caso o campo exista, false caso contrário.
     */
    private function existField(string $field): bool
    {
        return isset($this->Post->$field);
    }

    /**
     * Valida o tipo do campo.
     * @param string $field Campo a ser validado.
     * @param Field $filter Tipo do campo a ser validado.
     * @return bool True caso o tipo do campo seja válido, false caso contrário.
     */
    private function validateType(string $field, Field $filter): bool
    {
        return $filter->validate($this->Post->$field);
    }
}
