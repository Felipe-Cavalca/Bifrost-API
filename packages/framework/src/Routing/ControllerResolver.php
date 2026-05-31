<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\AfterResponseAttribute;
use Bifrost\Framework\Contracts\BeforeRequestAttribute;
use Bifrost\Framework\Contracts\HttpAttribute;
use Bifrost\Framework\Contracts\RequestValidatorAttribute;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use ReflectionMethod;
use RuntimeException;

/**
 * Resolve e executa handlers de rota.
 *
 * Tambem aplica validators declarados por attributes antes de chamar a action.
 */
final class ControllerResolver
{
    /**
     * @param Container $container Container usado para resolver controllers registrados.
     */
    public function __construct(private readonly Container $container)
    {
    }

    /**
     * Executa um handler de rota.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     * @return mixed Resultado bruto da action/callable.
     */
    public function invoke(mixed $handler, Request $request): mixed
    {
        if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
            $controller = $this->container->has($handler[0])
                ? $this->container->get($handler[0])
                : new $handler[0]();

            $method = (string) $handler[1];
            $validationResponse = $this->validateAttributes($controller, $method, $request);
            if ($validationResponse instanceof Response) {
                return $validationResponse;
            }

            $beforeResponse = $this->runBeforeAttributes($controller, $method, $request);
            if ($beforeResponse instanceof Response) {
                return $beforeResponse;
            }

            $result = $controller->{$method}($request);

            return $this->runAfterAttributes($controller, $method, $request, $result);
        }

        if (is_callable($handler)) {
            return $handler($request, $this->container);
        }

        throw new RuntimeException('Handler de rota invalido.');
    }

    /**
     * Retorna metadados dos attributes HTTP de uma action.
     *
     * @param mixed $handler Par [Controller::class, 'metodo'].
     * @return array<string, mixed>
     */
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

    private function runBeforeAttributes(object $controller, string $method, Request $request): ?Response
    {
        $reflection = new ReflectionMethod($controller, $method);

        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (!$instance instanceof BeforeRequestAttribute) {
                continue;
            }

            $response = $instance->before($request, $this->container);
            if ($response instanceof Response) {
                return $response;
            }
        }

        return null;
    }

    private function runAfterAttributes(object $controller, string $method, Request $request, mixed $result): mixed
    {
        $reflection = new ReflectionMethod($controller, $method);
        $attributes = array_reverse($reflection->getAttributes());
        $response = null;

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if (!$instance instanceof AfterResponseAttribute) {
                continue;
            }

            $response ??= $this->responseFromResult($result);
            $replacement = $instance->after($request, $response, $this->container);
            if ($replacement instanceof Response) {
                $response = $replacement;
            }
        }

        return $response ?? $result;
    }

    private function responseFromResult(mixed $result): Response
    {
        return Response::fromResult($result);
    }
}
