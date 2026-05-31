<?php

declare(strict_types=1);

namespace Bifrost\Framework\Routing;

use Bifrost\Framework\Http\Request;
use ReflectionMethod;

/**
 * Resolve actions HTTP pela convencao /controller/action.
 *
 * Rotas registradas explicitamente continuam com prioridade. Este resolvedor
 * atua apenas como fallback e limita a busca ao namespace configurado.
 */
final class ConventionRouteResolver
{
    /**
     * @param string $controllerNamespace Namespace base dos controllers da aplicacao.
     * @param string $controllerSuffix Sufixo usado pelas classes de controller.
     * @param string $defaultAction Action usada quando a URL informa apenas o controller.
     */
    public function __construct(
        private readonly string $controllerNamespace = 'App\\Http\\Controller',
        private readonly string $controllerSuffix = 'Controller',
        private readonly string $defaultAction = 'index'
    ) {
    }

    /**
     * Busca um handler seguindo a convencao /controller/action.
     *
     * @return array{class-string, string}|null Par [Controller::class, 'action'] ou null quando a URL nao e valida.
     */
    public function resolve(Request $request): ?array
    {
        $segments = $this->segments($request->path());
        if ($segments === null) {
            return null;
        }

        [$controller, $action] = $segments;
        $controllerClass = $this->controllerClass($controller);
        if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
            return null;
        }

        $reflection = new ReflectionMethod($controllerClass, $action);
        if (!$reflection->isPublic() || $reflection->isConstructor() || $reflection->isDestructor()) {
            return null;
        }

        return [$controllerClass, $action];
    }

    /**
     * @return array{string, string}|null
     */
    private function segments(string $path): ?array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
        if ($segments === [] || count($segments) > 2) {
            return null;
        }

        $controller = $segments[0];
        $action = $segments[1] ?? $this->defaultAction;
        if (!$this->isValidSegment($controller) || !$this->isValidSegment($action)) {
            return null;
        }

        return [$controller, $action];
    }

    private function controllerClass(string $controller): string
    {
        return rtrim($this->controllerNamespace, '\\')
            . '\\'
            . ucfirst($controller)
            . $this->controllerSuffix;
    }

    private function isValidSegment(string $segment): bool
    {
        return preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $segment) === 1;
    }
}
