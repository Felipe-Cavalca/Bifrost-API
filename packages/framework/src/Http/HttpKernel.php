<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use Bifrost\Framework\Routing\ControllerResolver;
use Bifrost\Framework\Routing\Router;
use Throwable;

final class HttpKernel
{
    /** @var list<callable> */
    private array $middleware = [];

    public function __construct(
        private readonly Router $router,
        private readonly ControllerResolver $controllerResolver,
        private readonly bool $debug = false
    ) {
    }

    public function middleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function handle(Request $request): Response
    {
        $dispatcher = fn (Request $incoming): Response => $this->dispatch($incoming);

        foreach (array_reverse($this->middleware) as $middleware) {
            $next = $dispatcher;
            $dispatcher = fn (Request $incoming): Response => $middleware($incoming, $next);
        }

        try {
            return $dispatcher($request);
        } catch (Throwable $exception) {
            return Response::json(
                payload: [
                    'message' => $this->debug ? $exception->getMessage() : 'Internal Server Error',
                ],
                status: 500
            );
        }
    }

    private function dispatch(Request $request): Response
    {
        $route = $this->router->match($request);
        if ($route === null) {
            $allowedMethods = $this->router->allowedMethods($request->path());
            if ($allowedMethods !== []) {
                return Response::json(
                    payload: ['message' => 'Method Not Allowed'],
                    status: 405,
                    headers: ['Allow' => implode(', ', $allowedMethods)]
                );
            }

            return Response::json(payload: ['message' => 'Not Found'], status: 404);
        }

        $result = $this->controllerResolver->invoke($route->handler(), $request);
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json(payload: $result);
        }

        return Response::text((string) $result);
    }
}
