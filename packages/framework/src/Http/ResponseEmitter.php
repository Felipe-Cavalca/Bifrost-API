<?php

declare(strict_types=1);

namespace Bifrost\Framework\Http;

/**
 * Envia uma Response para o runtime HTTP do PHP.
 */
final class ResponseEmitter
{
    /**
     * Define status, headers e imprime o corpo da resposta.
     */
    public function emit(Response $response): void
    {
        http_response_code($response->status());
        foreach ($response->headers() as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $response->body();
    }
}
