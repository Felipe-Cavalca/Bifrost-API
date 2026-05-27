<?php

declare(strict_types=1);

use App\Http\Controller\HealthController;

$app->get(path: '/health', handler: [HealthController::class, 'show']);
