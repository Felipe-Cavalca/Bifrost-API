<?php

declare(strict_types=1);

namespace Bifrost\Framework;

use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Http\HttpKernel;
use Bifrost\Framework\Http\HttpMethod;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use Bifrost\Framework\Http\ResponseEmitter;
use Bifrost\Framework\Routing\ControllerResolver;
use Bifrost\Framework\Routing\ConventionRouteResolver;
use Bifrost\Framework\Routing\Router;

/**
 * Ponto principal de configuracao e execucao de uma aplicacao Bifrost.
 *
 * Registra rotas, middlewares, extensoes e despacha requests para o kernel HTTP.
 */
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
            conventionRouteResolver: new ConventionRouteResolver(),
            debug: $debug
        );
        $this->emitter = new ResponseEmitter();
    }

    /**
     * Cria uma aplicacao Bifrost.
     *
     * @param bool $debug Quando true, respostas 500 podem expor a mensagem da excecao.
     */
    public static function create(bool $debug = false): self
    {
        return new self(debug: $debug);
    }

    /**
     * Retorna o container de dependencias da aplicacao.
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Registra uma extensao do framework.
     */
    public function extend(Extension $extension): self
    {
        $extension->register($this);

        return $this;
    }

    /**
     * Adiciona um middleware HTTP.
     *
     * O middleware recebe Request e next callable, e deve retornar Response.
     */
    public function middleware(callable $middleware): self
    {
        $this->kernel->middleware($middleware);

        return $this;
    }

    /**
     * Registra uma rota HTTP.
     *
     * @param string|HttpMethod $method Metodo HTTP aceito pela rota.
     * @param string $path Path da rota, com ou sem barra inicial.
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function route(string|HttpMethod $method, string $path, mixed $handler): self
    {
        $this->router->add(method: $method, path: $path, handler: $handler);

        return $this;
    }

    /**
     * Registra uma rota GET.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function get(string $path, mixed $handler): self
    {
        return $this->route(method: HttpMethod::Get, path: $path, handler: $handler);
    }

    /**
     * Registra uma rota POST.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function post(string $path, mixed $handler): self
    {
        return $this->route(method: HttpMethod::Post, path: $path, handler: $handler);
    }

    /**
     * Registra uma rota PUT.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function put(string $path, mixed $handler): self
    {
        return $this->route(method: HttpMethod::Put, path: $path, handler: $handler);
    }

    /**
     * Registra uma rota PATCH.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function patch(string $path, mixed $handler): self
    {
        return $this->route(method: HttpMethod::Patch, path: $path, handler: $handler);
    }

    /**
     * Registra uma rota DELETE.
     *
     * @param mixed $handler Callable ou par [Controller::class, 'metodo'].
     */
    public function delete(string $path, mixed $handler): self
    {
        return $this->route(method: HttpMethod::Delete, path: $path, handler: $handler);
    }

    /**
     * Processa uma request e retorna uma resposta.
     */
    public function handle(Request $request): Response
    {
        return $this->kernel->handle($request);
    }

    /**
     * Envia a resposta HTTP para o cliente.
     */
    public function emit(Response $response): void
    {
        $this->emitter->emit($response);
    }
}
