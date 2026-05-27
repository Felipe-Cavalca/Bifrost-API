<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use DateTimeImmutable;

interface Storage
{
    public function put(string $key, string $body, array $options = []): array;

    public function get(string $key, array $options = []): array;

    public function delete(string $key, array $options = []): array;

    public function temporaryUrl(
        string $key,
        ?DateTimeImmutable $expiresAt = null,
        array $options = []
    ): string;
}
