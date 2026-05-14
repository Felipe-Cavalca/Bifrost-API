<?php

namespace Bifrost\Interface;

use Bifrost\DataTypes\DateTime;
use Bifrost\DataTypes\StorageKey;

interface Storage
{
    public function put(StorageKey $key, string $body, array $options = []): array;

    public function get(StorageKey $key, array $options = []): array;

    public function delete(StorageKey $key, array $options = []): array;

    public function createPresignedUrl(
        StorageKey $key,
        DateTime $expires = new DateTime('+15 minutes'),
        array $options = []
    ): string;

    public function getClient(): mixed;
}
