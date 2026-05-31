<?php

declare(strict_types=1);

namespace Bifrost\Framework;

use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Http\HttpKernel;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Http\ResponseEmitter;
use Bifrost\Framework\Routing\ControllerResolver;
use Bifrost\Framework\Routing\Router;

final class Application
{
    private readonly Router $router;
    private readonly Container $container;
    private readonly HttpKernel $kernel;
    private readonly ResponseEmitter $emitter;

    private function __construct(bool $debug = false)
    {
        $this->router = new Router();
        $this->container = new Container();
        $this->kernel = new HttpKernel(
            router: $this->router,
            controllerResolver: new ControllerResolver($this->container),
            debug: $debug
        );
        $this->emitter = new ResponseEmitter();
    }

    public static function create(bool $debug = false): self
    {
        return new self(debug: $debug);
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function extend(Extension $extension): self
    {
        $extension->register($this);

        return $this;
    }

    public function middleware(callable $middleware): self
    {
        $this->kernel->middleware($middleware);

        return $this;
    }

    public function route(string $method, string $path, mixed $handler): self
    {
        $this->router->add(method: $method, path: $path, handler: $handler);

        return $this;
    }

    public function get(string $path, mixed $handler): self
    {
        return $this->route(method: 'GET', path: $path, handler: $handler);
    }

    public function post(string $path, mixed $handler): self
    {
        return $this->route(method: 'POST', path: $path, handler: $handler);
    }

    public function put(string $path, mixed $handler): self
    {
        return $this->route(method: 'PUT', path: $path, handler: $handler);
    }

    public function patch(string $path, mixed $handler): self
    {
        return $this->route(method: 'PATCH', path: $path, handler: $handler);
    }

    public function delete(string $path, mixed $handler): self
    {
        return $this->route(method: 'DELETE', path: $path, handler: $handler);
    }

    public function handle(Request $request): Response
    {
        return $this->kernel->handle($request);
    }

    public function emit(Response $response): void
    {
        $this->emitter->emit($response);
    }
}
