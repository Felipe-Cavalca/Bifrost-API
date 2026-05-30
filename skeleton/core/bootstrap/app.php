<?php

declare(strict_types=1);

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

// As rotas ficam separadas para manter o bootstrap pequeno e previsivel.
require dirname(__DIR__) . '/routes/api.php';

return $app;
