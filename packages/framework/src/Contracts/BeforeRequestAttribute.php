<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Container;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

/**
 * Contrato para attributes executados antes da action do controller.
 */
interface BeforeRequestAttribute extends HttpAttribute
{
    /**
     * Executa logica antes da action.
     *
     * @return Response|null Retorne Response para interromper o fluxo, ou null para continuar.
     */
    public function before(Request $request, Container $container): ?Response;
}
