<?php

namespace Bifrost\Integration;

class Apcu
{
    static function store(string $key, mixed $value, int $ttl = 3600): bool {
        return apcu_store($key, $value, $ttl);
    }

    static function fetch(string $key): mixed {
        return apcu_fetch($key);
    }

    static function exists(string $key): bool {
        return apcu_exists($key);
    }

    static function delete(string $key): bool {
        return apcu_delete($key);
    }
}
