<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use Bifrost\Framework\Exceptions\HttpException;
use Bifrost\Framework\Routing\ControllerResolver;
use Bifrost\Framework\Routing\Router;
use JsonException;
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
            return $this->finalizeResponse($request, $dispatcher($request));
        } catch (Throwable $exception) {
            return $this->finalizeResponse($request, $this->exceptionResponse($exception));
        }
    }

    private function dispatch(Request $request): Response
    {
        $route = $this->router->match($request);
        if ($route === null) {
            $allowedMethods = $this->router->allowedMethods($request->path());
            if ($request->method() === 'OPTIONS' && $allowedMethods !== []) {
                $routeForOptions = $this->router->firstRouteForPath($request->path());

                return Response::json(payload: [
                    'attributes' => $routeForOptions === null
                        ? []
                        : $this->controllerResolver->options($routeForOptions->handler()),
                ]);
            }

            if ($allowedMethods !== []) {
                return Response::json(
                    payload: ['message' => 'Method Not Allowed'],
                    status: 405,
                    headers: ['Allow' => implode(', ', $allowedMethods)]
                );
            }

            return Response::notFound();
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

    /**
     * @throws JsonException
     */
    private function exceptionResponse(Throwable $exception): Response
    {
        if ($exception instanceof HttpException) {
            $payload = ['message' => $exception->getMessage()];
            if ($exception->errors() !== []) {
                $payload['errors'] = $exception->errors();
            }

            return Response::json(
                payload: $payload,
                status: $exception->status(),
                headers: $exception->headers()
            );
        }

        return Response::internalServerError(
            message: $this->debug ? $exception->getMessage() : 'Internal Server Error'
        );
    }

    /**
     * @throws JsonException
     */
    private function finalizeResponse(Request $request, Response $response): Response
    {
        return $this->withRequestIdPayload($request, $response)
            ->withHeader('X-Request-Id', $request->requestId());
    }

    /**
     * @throws JsonException
     */
    private function withRequestIdPayload(Request $request, Response $response): Response
    {
        if ($response->status() < 400 || !$this->isJsonResponse($response)) {
            return $response;
        }

        $payload = json_decode($response->body(), true);
        if (!is_array($payload) || array_key_exists('request_id', $payload)) {
            return $response;
        }

        $payload['request_id'] = $request->requestId();

        return Response::json(payload: $payload, status: $response->status(), headers: $response->headers());
    }

    private function isJsonResponse(Response $response): bool
    {
        foreach ($response->headers() as $name => $value) {
            if (strtolower((string) $name) !== 'content-type') {
                continue;
            }

            return str_contains(strtolower((string) $value), 'application/json');
        }

        return false;
    }
}
