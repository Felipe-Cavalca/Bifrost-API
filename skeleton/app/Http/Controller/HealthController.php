<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

/**
 * Controller de health check do projeto base.
 */
final class HealthController
{
    /**
     * Retorna status basico da aplicacao.
     */
    public static function show(Request $request): Response
    {
        return Response::json(payload: ['status' => 'ok']);
    }
}
