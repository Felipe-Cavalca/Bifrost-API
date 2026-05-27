<?php

declare(strict_types=1);

namespace Bifrost\Framework;

use RuntimeException;

final class Container
{
    /** @var array<string, callable|object> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function bind(string $id, callable|object $binding): void
    {
        $this->bindings[$id] = $binding;
        unset($this->instances[$id]);
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
        unset($this->bindings[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function get(string $id): object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $binding = $this->bindings[$id] ?? null;
        if ($binding === null) {
            throw new RuntimeException("Servico '{$id}' nao foi registrado.");
        }

        $instance = is_callable($binding) ? $binding($this) : $binding;
        if (!is_object($instance)) {
            throw new RuntimeException("Servico '{$id}' deve resolver para um objeto.");
        }

        return $this->instances[$id] = $instance;
    }
}
