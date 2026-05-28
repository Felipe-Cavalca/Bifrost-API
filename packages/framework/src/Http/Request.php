<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

final class Request
{
    private readonly array $headers;
    private readonly string $requestId;

    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        array $headers = [],
        ?string $requestId = null
    ) {
        $this->headers = self::normalizeHeaders($headers);
        $this->requestId = self::resolveRequestId($this->headers, $requestId);
    }

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

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function path(): string
    {
        $path = '/' . ltrim($this->path, '/');

        return $path !== '/' ? rtrim($path, '/') : $path;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : ($this->query[$key] ?? $default);
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->body : ($this->body[$key] ?? $default);
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

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
