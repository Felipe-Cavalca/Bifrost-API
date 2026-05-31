<?php

declare(strict_types=1);

use Bifrost\Framework\Application;

$app = Application::create(
    debug: filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL)
);

foreach (require dirname(__DIR__) . '/config/extensions.php' as $extension) {
    $app->extend($extension);
}

require dirname(__DIR__) . '/routes/api.php';

return $app;
