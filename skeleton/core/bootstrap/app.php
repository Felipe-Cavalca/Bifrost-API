<?php

declare(strict_types=1);

use App\Http\HttpRoutes;
use Bifrost\Framework\Application;

// Este arquivo monta a aplicacao. Mantenha aqui apenas configuracao de boot:
// extensoes opcionais, rotas e outros registros globais do projeto.
$app = Application::create(
    debug: filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL)
);

// Cada extensao adiciona uma capacidade opcional, como cache, banco ou logs.
foreach (require dirname(__DIR__) . '/config/extensions.php' as $extension) {
    $app->extend($extension);
}

// Rotas explicitas ficam no app e servem para aliases ou URLs customizadas.
// URLs /controller/action funcionam por convencao sem registro manual.
HttpRoutes::register($app);

return $app;
