<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use Bifrost\Framework\Contracts\Responseable;
use JsonException;

/**
 * Representa uma resposta HTTP imutavel.
 *
 * Use os factories como json(), created(), badRequest() e text() para construir
 * respostas de controller com status e headers consistentes.
 */
final class Response
{
    /**
     * @param string $body Corpo bruto da resposta.
     * @param int $status Codigo HTTP da resposta.
     * @param array<string, string> $headers Headers HTTP da resposta.
     */
    public function __construct(
        private readonly string $body = '',
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    /**
     * Cria uma resposta JSON.
     *
     * @param mixed $payload Payload serializado para JSON.
     * @param int $status Codigo HTTP da resposta.
     * @param array<string, string> $headers Headers adicionais.
     * @throws JsonException
     */
    public static function json(mixed $payload, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        return new self(
            body: json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            status: $status,
            headers: $headers
        );
    }

    /**
     * Converte o retorno de um controller em resposta HTTP.
     *
     * Objetos arbitrarios nao sao serializados automaticamente. Implemente
     * Responseable para declarar explicitamente quais dados podem ser expostos.
     *
     * @param mixed $result Resultado retornado pelo controller.
     * @return self Resposta HTTP pronta para emissao.
     * @throws JsonException
     */
    public static function fromResult(mixed $result): self
    {
        if ($result instanceof self) {
            return $result;
        }

        if ($result instanceof Responseable) {
            return self::json(payload: $result->jsonSerialize());
        }

        if (is_array($result)) {
            return self::json(payload: $result);
        }

        return self::text((string) $result);
    }

    /**
     * Cria uma resposta JSON 201 Created.
     *
     * @param array<string, mixed> $payload Payload serializado para JSON.
     * @param array<string, string> $headers Headers adicionais.
     * @throws JsonException
     */
    public static function created(array $payload = [], array $headers = []): self
    {
        return self::json(payload: $payload, status: 201, headers: $headers);
    }

    /**
     * Cria uma resposta JSON 400 Bad Request.
     *
     * @param array<string, mixed> $errors Erros de validacao ou detalhes seguros para o cliente.
     * @param array<string, string> $headers Headers adicionais.
     * @throws JsonException
     */
    public static function badRequest(string $message = 'Bad Request', array $errors = [], array $headers = []): self
    {
        $payload = ['message' => $message];
        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return self::json(payload: $payload, status: 400, headers: $headers);
    }

    /**
     * Cria uma resposta JSON 404 Not Found.
     *
     * @param array<string, string> $headers Headers adicionais.
     * @throws JsonException
     */
    public static function notFound(string $message = 'Not Found', array $headers = []): self
    {
        return self::json(payload: ['message' => $message], status: 404, headers: $headers);
    }

    /**
     * Cria uma resposta JSON 500 Internal Server Error.
     *
     * @param array<string, string> $headers Headers adicionais.
     * @throws JsonException
     */
    public static function internalServerError(string $message = 'Internal Server Error', array $headers = []): self
    {
        return self::json(payload: ['message' => $message], status: 500, headers: $headers);
    }

    /**
     * Cria uma resposta de texto puro.
     *
     * @param array<string, string> $headers Headers adicionais.
     */
    public static function text(string $body, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] ??= 'text/plain; charset=utf-8';

        return new self(body: $body, status: $status, headers: $headers);
    }

    /**
     * Retorna uma nova resposta com o header informado.
     */
    public function withHeader(string $name, string $value): self
    {
        return new self(
            body: $this->body,
            status: $this->status,
            headers: array_merge($this->headers, [$name => $value])
        );
    }

    /**
     * Retorna uma nova resposta com outro corpo.
     */
    public function withBody(string $body): self
    {
        return new self(body: $body, status: $this->status, headers: $this->headers);
    }

    /**
     * Retorna o corpo bruto da resposta.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Retorna o codigo HTTP da resposta.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, string> Headers HTTP da resposta.
     */
    public function headers(): array
    {
        return $this->headers;
    }
}
