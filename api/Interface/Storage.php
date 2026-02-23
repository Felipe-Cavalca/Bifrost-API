<?php

namespace Bifrost\Interface;

interface Storage
{
    public function put(string $key, string $body, array $options = []): array;

    public function get(string $key, array $options = []): array;

    public function delete(string $key, array $options = []): array;

    public function createPresignedUrl(string $key, string $expires = "+15 minutes", array $options = []): string;

    public function getClient(): mixed;
}
