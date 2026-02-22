<?php

namespace Bifrost\Interface;

interface Cache
{
    public function set(string $key, mixed $value, int $expire = 1): bool;

    public function get(string $key, mixed $value = null, int $expire = 1): mixed;

    public function exists(string $key): bool;

    public function del(string $key): bool;
}
