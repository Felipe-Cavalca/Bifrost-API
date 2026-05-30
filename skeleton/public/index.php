<?php

declare(strict_types=1);

use Bifrost\Framework\Http\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

// public/ deve ser a unica pasta exposta pelo servidor HTTP. O restante do
// projeto, incluindo configuracoes e dependencias, fica fora do document root.
$app = require dirname(__DIR__) . '/core/bootstrap/app.php';
$response = $app->handle(Request::fromGlobals());

$app->emit($response);
