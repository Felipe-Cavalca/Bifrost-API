<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use JsonException;

final class Response
{
    public function __construct(
        private readonly string $body = '',
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    /**
     * @throws JsonException
     */
    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';

        return new self(
            body: json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            status: $status,
            headers: $headers
        );
    }

    public static function text(string $body, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] ??= 'text/plain; charset=utf-8';

        return new self(body: $body, status: $status, headers: $headers);
    }

    public function withHeader(string $name, string $value): self
    {
        return new self(
            body: $this->body,
            status: $this->status,
            headers: array_merge($this->headers, [$name => $value])
        );
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
