<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

/**
 * Representa uma rota HTTP registrada no router.
 */
final class Route
{
    /**
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly mixed $handler
    ) {
    }

    /**
     * Retorna o metodo HTTP da rota.
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Retorna o path normalizado da rota.
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Retorna o handler da rota.
     *
     * @return mixed Callable ou par [Controller::class, 'metodo'].
     */
    public function handler(): mixed
    {
        return $this->handler;
    }
}
