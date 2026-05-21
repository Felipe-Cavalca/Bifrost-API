<?php

namespace Bifrost\Enum;

Enum Routes: string
{
    /**
     * Enumeracao de rotas do sistema.
     * Cada rota e definida como um caso do enum, representando o caminho da rota.
     */
    case login = "auth/login";
    case logout = "auth/logout";
    case health = "index/health";
    case ping = "index/ping";

    /**
     * Converte o caminho da requisicao para o formato de enumeracao.
     * @param string $path O caminho da requisicao, como "payments-sumary".
     * @return self|null Retorna a enumeracao correspondente ou null se nao encontrado.
     */
    public static function fromRequest(string $path): ?self
    {
        static $routesByName = null;

        if ($routesByName === null) {
            $routesByName = [];
            foreach (self::cases() as $route) {
                $routesByName[$route->name] = $route;
            }
        }

        if (isset($routesByName[$path])) {
            return $routesByName[$path];
        }

        $converted = preg_replace_callback(
            '/[-\/](\w)/',
            fn($m) => strtoupper($m[1]),
            $path
        );

        return is_string($converted) ? ($routesByName[$converted] ?? null) : null;
    }
}
