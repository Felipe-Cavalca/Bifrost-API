<?php

namespace Bifrost\Controller;

use Bifrost\Class\HttpResponse;
use Bifrost\Interface\Controller;
use Bifrost\Interface\Responseable;

class Index implements Controller
{

    public function index(): Responseable
    {
        return HttpResponse::success(message: "Operation completed successfully");
    }

    public function health(): Responseable
    {
        return HttpResponse::success(
            message: 'OK',
            data: [
                'status' => 'healthy',
                'service' => 'bifrost-api',
                'checked_at' => gmdate('c'),
            ]
        );
    }
}
