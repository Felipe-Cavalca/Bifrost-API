<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use Bifrost\Framework\Exceptions\HttpException;
use Bifrost\Framework\Routing\ConventionRouteResolver;
use Bifrost\Framework\Routing\ControllerResolver;
use Bifrost\Framework\Routing\Router;
use JsonException;
use Throwable;

/**
 * Executa o lifecycle HTTP da aplicacao.
 *
 * Aplica middlewares, resolve rotas, converte resultados de controllers em Response
 * e padroniza respostas de erro com X-Request-Id.
 */
final class HttpKernel
{
    /** @var list<callable> */
    private array $middleware = [];

    /**
     * @param Router $router Roteador HTTP da aplicacao.
     * @param ControllerResolver $controllerResolver Resolvedor de handlers e controllers.
     * @param ConventionRouteResolver $conventionRouteResolver Resolvedor fallback para URLs /controller/action.
     * @param bool $debug Quando true, respostas 500 podem expor a mensagem da excecao.
     */
    public function __construct(
        private readonly Router $router,
        private readonly ControllerResolver $controllerResolver,
        private readonly ConventionRouteResolver $conventionRouteResolver,
        private readonly bool $debug = false
    ) {
    }

    /**
     * Registra um middleware no pipeline HTTP.
     *
     * O callable recebe Request e next callable, e deve retornar Response.
     */
    public function middleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Processa uma request completa e sempre retorna uma Response.
     */
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

            $conventionHandler = $this->conventionRouteResolver->resolve($request);
            if ($conventionHandler === null) {
                return Response::notFound();
            }

            return Response::fromResult(
                $this->controllerResolver->invoke($conventionHandler, $request)
            );
        }

        return Response::fromResult(
            $this->controllerResolver->invoke($route->handler(), $request)
        );
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
