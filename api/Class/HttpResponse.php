<?php

namespace Bifrost\Class;

use Bifrost\Enum\HttpStatusCode;
use Bifrost\Interface\Responseable;

/**
 * Classe para tratar respostas HTTP.
 */
class HttpResponse implements Responseable
{

    /**
     * Representação da resposta HTTP.
     * @param HttpStatusCode $status Status code da resposta HTTP
     * @param Responseable|null|string $message Mensagem opcional da resposta HTTP
     * @param Responseable|null|array $data Dados opcionais da resposta HTTP
     * @param Responseable|null|array $errors Erros opcionais da resposta HTTP
     * @param array $additionalInfo Informações adicionais opcionais da resposta HTTP
     */
    public function __construct(
        public HttpStatusCode $status = HttpStatusCode::INTERNAL_SERVER_ERROR,
        public Responseable|null|string $message = null,
        private Responseable|null|array $data = null,
        private Responseable|null|array $errors = null,
        private array $additionalInfo = []
    ) {}

    /**
     * Serializa a resposta HTTP para JSON e seta status code da response
     * @return array Retorna a resposta HTTP serializada como um array
     */
    public function jsonSerialize(): array
    {
        http_response_code($this->status->value);
        return array_merge([
            "status" => $this->status->value,
            "message" => $this->message ?? $this->status->message(),
            "data" => $this->data,
            "errors" => $this->errors,
        ], $this->additionalInfo);
    }

    /**
     * Adiciona informações adicionais à resposta HTTP.
     * @param array $additionalInfo Informações adicionais a serem adicionadas
     * @return void
     */
    public function addAditionalInfo(array $additionalInfo): void
    {
        $this->additionalInfo = array_merge($this->additionalInfo, $additionalInfo);
    }

    // Métodos estáticos para construir respostas comuns

    /**
     * Constrói uma resposta de sucesso.
     * @param string|null $message Mensagem a ser retornada.
     * @param Responseable|array|null $data Dados adicionais sobre o endpoint.
     * @return self Instância da classe HttpResponse com status 200 (OK).
     */
    public static function success(?string $message = null, Responseable|array|null $data = null): self
    {
        return new self(
            status: HttpStatusCode::OK,
            message: $message,
            data: $data
        );
    }

    /**
     * Constrói uma resposta para criação de recursos.
     * @param string $objName Nome do objeto criado.
     * @param Responseable $data Dados sobre o recurso criado.
     * @return self Instância da classe HttpResponse com status 201 (Created).
     */
    public static function created(string $objName, Responseable $data): self
    {
        return new self(
            status: HttpStatusCode::CREATED,
            message: $objName . " created successfully",
            data: $data
        );
    }

    /**
     * Retorna as opções de métodos permitidos para o cliente.
     * @param array $methods Métodos permitidos.
     * @return self Resposta de opções de métodos permitidos para o cliente.
     */
    public static function options(array $methods): self
    {
        header("Access-Control-Allow-Methods: " . implode(", ", $methods));
        return new self(
            status: HttpStatusCode::OK,
            message: "Allow Methods",
            additionalInfo: ["methods" => $methods]
        );
    }

    /**
     * Retorna a resposta de não encontrado para o cliente
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @param string $message Mensagem a ser retornada.
     * @return self Resposta de não encontrado para o cliente.
     */
    public static function notFound(Responseable|array $errors, ?string $message = null): self
    {
        return new self(
            status: HttpStatusCode::NOT_FOUND,
            message: $message,
            errors: $errors
        );
    }

    /**
     * Retorna a resposta de método não permitido para o cliente
     * @param string $message Mensagem a ser retornada.
     * @return self Resposta de método não permitido para o cliente.
     */
    public static function methodNotAllowed(string $message): self
    {
        return new self(
            status: HttpStatusCode::METHOD_NOT_ALLOWED,
            message: $message,
            errors: [
                "method" => $_SERVER["REQUEST_METHOD"]
            ]
        );
    }

    /**
     * Retorna a resposta de erro de requisição para o cliente
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @param string $message Mensagem de erro a ser retornada.
     * @return self Resposta de erro de requisição para o cliente.
     */
    public static function badRequest(Responseable|array $errors, ?string $message = null): self
    {
        return new self(
            status: HttpStatusCode::BAD_REQUEST,
            message: $message,
            errors: $errors
        );
    }

    /**
     * Retorna a resposta de conflito para o cliente
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @param string $message Mensagem de erro a ser retornada.
     * @return self Resposta de conflito para o cliente.
     */
    public static function conflict(Responseable|array $errors, ?string $message = null): self
    {
        return new self(
            status: HttpStatusCode::CONFLICT,
            message: $message,
            errors: $errors
        );
    }

    /**
     * Retorna a resposta de erro interno do servidor para o cliente
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @param string $message Mensagem de erro a ser retornada.
     * @return self Resposta de erro interno do servidor para o cliente.
     */
    public static function internalServerError(Responseable|array $errors, ?string $message = null): self
    {
        return new self(
            status: HttpStatusCode::INTERNAL_SERVER_ERROR,
            message: $message,
            errors: $errors
        );
    }

    /**
     * Retorna a resposta de erro de autenticação para o cliente
     * @param string $message Mensagem de erro a ser retornada.
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @return self Resposta de erro de autenticação para o cliente.
     */
    public static function unauthorized(string $message, Responseable|array $errors = []): self
    {
        return new self(
            status: HttpStatusCode::UNAUTHORIZED,
            message: $message,
            errors: $errors
        );
    }

    /**
     * Retorna a resposta de erro de permissão para o cliente
     * @param string $message Mensagem de erro a ser retornada.
     * @param Responseable|array $errors Dados adicionais sobre o erro.
     * @return self Resposta de erro de permissão para o cliente.
     */
    public static function forbidden(string $message, Responseable|array $errors = []): self
    {
        return new self(
            status: HttpStatusCode::FORBIDDEN,
            message: $message,
            errors: $errors
        );
    }
}
