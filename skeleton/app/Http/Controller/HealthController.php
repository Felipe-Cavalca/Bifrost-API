<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;

final class HealthController
{
    public static function show(Request $request): Response
    {
        return Response::json(payload: ['status' => 'ok']);
    }
}
