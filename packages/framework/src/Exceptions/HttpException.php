<?php

declare(strict_types=1);

namespace Bifrost\Framework\Exceptions;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $status = 500,
        private readonly array $errors = [],
        private readonly array $headers = []
    ) {
        parent::__construct($message);
    }

    public static function badRequest(string $message = 'Bad Request', array $errors = [], array $headers = []): self
    {
        return new self(message: $message, status: 400, errors: $errors, headers: $headers);
    }

    public static function notFound(string $message = 'Not Found', array $headers = []): self
    {
        return new self(message: $message, status: 404, headers: $headers);
    }

    public static function internalServerError(string $message = 'Internal Server Error', array $headers = []): self
    {
        return new self(message: $message, status: 500, headers: $headers);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
