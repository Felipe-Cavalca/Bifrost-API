<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\HttpAttribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use ReflectionMethod;
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

            $validationResponse = $this->validateAttributes($controller, (string) $handler[1], $request);
            if ($validationResponse instanceof Response) {
                return $validationResponse;
            }

            return $controller->{$handler[1]}($request);
        }

        if (is_callable($handler)) {
            return $handler($request, $this->container);
        }

        throw new RuntimeException('Handler de rota invalido.');
    }

    public function options(mixed $handler): array
    {
        if (!is_array($handler) || !isset($handler[0], $handler[1]) || !is_string($handler[0])) {
            return [];
        }

        $controller = $this->container->has($handler[0])
            ? $this->container->get($handler[0])
            : new $handler[0]();

        $reflection = new ReflectionMethod($controller, (string) $handler[1]);
        $options = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (!$instance instanceof HttpAttribute) {
                continue;
            }

            $options = array_merge($options, $instance->options());
        }

        return $options;
    }

    private function validateAttributes(object $controller, string $method, Request $request): ?Response
    {
        $reflection = new ReflectionMethod($controller, $method);

        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (!$instance instanceof RequestValidatorAttribute) {
                continue;
            }

            $response = $instance->validate($request);
            if ($response instanceof Response) {
                return $response;
            }
        }

        return null;
    }
}
