<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Container;
use Bifrost\Framework\Http\Request;
use RuntimeException;

final class ControllerResolver
{
    public function __construct(private readonly Container $container)
    {
    }

    public function invoke(mixed $handler, Request $request): mixed
    {
        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
            $controller = $this->container->has($handler[0])
                ? $this->container->get($handler[0])
                : new $handler[0]();

            return $controller->{$handler[1]}($request);
        }

        if (is_callable($handler)) {
            return $handler($request, $this->container);
        }

        throw new RuntimeException('Handler de rota invalido.');
    }
}
