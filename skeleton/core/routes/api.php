<?php

declare(strict_types=1);

use App\Http\Controller\HealthController;

// Rotas conectam uma URL a uma action. Controllers devem permanecer pequenos:
// converta os dados da Request e delegue regras de negocio para Services.
$app->get(path: '/health', handler: [HealthController::class, 'show']);
