<?php

namespace Bifrost\Enum;

Enum Routes: string
{

    /**
     * Enumeração de rotas do sistema.
     * Cada rota é definida como um caso do enum, representando o caminho da rota.
     */
    case login = "auth/login";
    case logout = "auth/logout";
    case health = "index/health";

    /**
     * Converte o caminho da requisição para o formato de enumeração.
     * @param string $path O caminho da requisição, como "payments-sumary".
     * @return self|null Retorna a enumeração correspondente ou null se não encontrado.
     */
    public static function fromRequest(string $path): ?self
    {
        $converted = preg_replace_callback(
            '/[-\/](\w)/',
            fn($m) => strtoupper($m[1]),
            $path
        );

        return self::tryFromName($converted);
    }

    /**
     * Tenta encontrar uma enumeração pelo nome.
     * @param string $name O nome da enumeração, como "paymentsSummary".
     * @return self|null Retorna a enumeração correspondente ou null se não encontrado.
     */
    private static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $route) {
            if ($route->name === $name) {
                return $route;
            }
        }
        return null;
    }
}
