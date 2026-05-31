<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

use InvalidArgumentException;

/**
 * Metodos HTTP aceitos pelo roteador e pela request.
 */
enum HttpMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Patch = 'PATCH';
    case Delete = 'DELETE';
    case Options = 'OPTIONS';
    case Head = 'HEAD';

    /**
     * Normaliza uma string ou enum para HttpMethod.
     *
     * @param string|self $method Metodo HTTP como string ou enum.
     * @return self Metodo HTTP normalizado.
     */
    public static function fromValue(string|self $method): self
    {
        if ($method instanceof self) {
            return $method;
        }

        return self::tryFrom(strtoupper($method))
            ?? throw new InvalidArgumentException(sprintf('Metodo HTTP invalido: %s.', $method));
    }
}
