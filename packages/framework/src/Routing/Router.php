<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Http\Request;

final class Router
{
    /** @var array<string, array<string, Route>> */
    private array $routes = [];

    public function add(string $method, string $path, mixed $handler): void
    {
        $method = strtoupper($method);
        $path = self::normalizePath($path);
        $this->routes[$path][$method] = new Route(method: $method, path: $path, handler: $handler);
    }

    public function match(Request $request): ?Route
    {
        return $this->routes[$request->path()][$request->method()] ?? null;
    }

    public function allowedMethods(string $path): array
    {
        return array_keys($this->routes[self::normalizePath($path)] ?? []);
    }

    public function firstRouteForPath(string $path): ?Route
    {
        $routes = $this->routes[self::normalizePath($path)] ?? [];

        return $routes === [] ? null : reset($routes);
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . ltrim($path, '/');

        return $path !== '/' ? rtrim($path, '/') : $path;
    }
}
