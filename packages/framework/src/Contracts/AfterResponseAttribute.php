<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Container;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

/**
 * Contrato para attributes executados depois da action do controller.
 */
interface AfterResponseAttribute extends HttpAttribute
{
    /**
     * Executa logica apos a action.
     *
     * Retorne uma nova Response quando precisar substituir a resposta original.
     */
    public function after(Request $request, Response $response, Container $container): ?Response;
}
