<?php

declare(strict_types=1);

namespace Bifrost\Framework\Contracts;

use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

/**
 * Contrato para attributes que validam uma request antes da action do controller.
 */
interface RequestValidatorAttribute extends HttpAttribute
{
    /**
     * Valida a request atual.
     *
     * @return Response|null Retorne null para permitir a action, ou Response para interromper o fluxo.
     */
    public function validate(Request $request): ?Response;
}
