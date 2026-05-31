<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Controller\HealthController;
use Bifrost\Framework\Application;

/**
 * Registra aliases e rotas que nao seguem a convencao /controller/action.
 *
 * A convencao ja permite acessar /health/show sem declarar uma rota. Este
 * alias oferece /health para health checks de infraestrutura.
 */
final class HttpRoutes
{
    public static function register(Application $app): void
    {
        $app->get(path: '/health', handler: [HealthController::class, 'show']);
    }
}
