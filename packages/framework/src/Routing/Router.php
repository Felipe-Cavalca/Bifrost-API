<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\HttpMethod;

/**
 * Registro e busca de rotas HTTP.
 */
final class Router
{
    /** @var array<string, array<string, Route>> */
    private array $routes = [];

    /**
     * Registra uma rota.
     *
     * @param string|HttpMethod $method Metodo HTTP aceito pela rota.
     * @param string $path Path da rota, com ou sem barra inicial.
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function add(string|HttpMethod $method, string $path, mixed $handler): void
    {
        $method = HttpMethod::fromValue($method)->value;
        $path = self::normalizePath($path);
        $this->routes[$path][$method] = new Route(method: $method, path: $path, handler: $handler);
    }

    /**
     * Busca a rota exata para method/path da request.
     */
    public function match(Request $request): ?Route
    {
        return $this->routes[$request->path()][$request->method()] ?? null;
    }

    /**
     * Retorna os metodos permitidos para um path.
     *
     * @return list<string>
     */
    public function allowedMethods(string $path): array
    {
        return array_keys($this->routes[self::normalizePath($path)] ?? []);
    }

    /**
     * Retorna a primeira rota registrada para um path, independente do metodo.
     */
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
