<?php

declare(strict_types=1);

use Bifrost\Framework\Http\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$response = $app->handle(Request::fromGlobals());

$app->emit($response);
