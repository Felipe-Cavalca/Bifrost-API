<?php

namespace Bifrost\Interface;

interface Cache
{
    public static function set(string $key, mixed $value, int $expire = 1): bool;

    public static function get(string $key, mixed $value = null, int $expire = 1): mixed;

    public static function exists(string $key): bool;

    public static function del(string $key): bool;
}
