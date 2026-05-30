<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

/**
 * Representa a request HTTP normalizada pelo Bifrost.
 *
 * Fornece acesso ao metodo, path, query string, corpo da request, headers e request-id.
 * Controllers recebem esta classe como entrada principal.
 */
final class Request
{
    private readonly HttpMethod $method;
    private readonly array $headers;
    private readonly string $requestId;

    /**
     * @param string|HttpMethod $method Metodo HTTP recebido.
     * @param string $path Path da request, com ou sem barra inicial.
     * @param array<string, mixed> $query Parametros de query string.
     * @param array<string, mixed> $body Corpo decodificado da request.
     * @param array<string, string> $headers Headers HTTP.
     * @param string|null $requestId Identificador da request. Se omitido, usa X-Request-Id ou gera um novo.
     */
    public function __construct(
        string|HttpMethod $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        array $headers = [],
        ?string $requestId = null
    ) {
        $this->method = HttpMethod::fromValue($method);
        $this->headers = self::normalizeHeaders($headers);
        $this->requestId = self::resolveRequestId($this->headers, $requestId);
    }

    /**
     * Cria uma request a partir das variaveis globais do PHP.
     */
    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?: '/');
        $rawBody = file_get_contents('php://input');
        $decoded = is_string($rawBody) && $rawBody !== '' ? json_decode($rawBody, true) : null;
        $body = is_array($decoded) ? $decoded : (is_array($_POST) ? $_POST : []);

        return new self(
            method: $method,
            path: $path,
            query: is_array($_GET) ? $_GET : [],
            body: $body,
            headers: self::headersFromServer($_SERVER)
        );
    }

    /**
     * Retorna o metodo HTTP em maiusculas.
     */
    public function method(): string
    {
        return $this->method->value;
    }

    /**
     * Retorna o metodo HTTP como enum.
     */
    public function httpMethod(): HttpMethod
    {
        return $this->method;
    }

    /**
     * Retorna o path normalizado, sempre com barra inicial e sem barra final.
     */
    public function path(): string
    {
        $path = '/' . ltrim($this->path, '/');

        return $path !== '/' ? rtrim($path, '/') : $path;
    }

    /**
     * Le parametros da query string.
     *
     * @param string|null $key Nome do parametro. Quando null, retorna todos os parametros.
     * @param mixed $default Valor usado quando o parametro nao existe.
     * @return mixed Valor do parametro, todos os parametros ou o default.
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    /**
     * Le valores do corpo da request.
     *
     * @param string|null $key Nome do campo. Quando null, retorna todo o corpo.
     * @param mixed $default Valor usado quando o campo nao existe.
     * @return mixed Valor do campo, corpo completo ou o default.
     */
    public function input(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->body : ($this->body[$key] ?? $default);
    }

    /**
     * Le um header HTTP por nome, sem diferenciar maiusculas/minusculas.
     *
     * @param string $name Nome do header, como Authorization ou X-Request-Id.
     * @param string|null $default Valor usado quando o header nao existe.
     */
    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Retorna o identificador da request para rastreamento.
     */
    public function requestId(): string
    {
        return $this->requestId;
    }

    private static function headersFromServer(array $server): array
    {
        $headers = [];
        foreach ($server as $name => $value) {
            if (!is_string($value) || !str_starts_with((string) $name, 'HTTP_')) {
                continue;
            }

            $key = strtolower(str_replace('_', '-', substr((string) $name, 5)));
            $headers[$key] = $value;
        }

        return $headers;
    }

    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            if (!is_string($name) || !is_string($value)) {
                continue;
            }

            $normalized[strtolower($name)] = $value;
        }

        return $normalized;
    }

    private static function resolveRequestId(array $headers, ?string $requestId): string
    {
        $requestId = trim((string) ($requestId ?? $headers['x-request-id'] ?? ''));
        if ($requestId !== '') {
            return $requestId;
        }

        return bin2hex(random_bytes(16));
    }
}
