<?php

declare(strict_types=1);

use Bifrost\Class\HttpResponse;
use Bifrost\Core\AppError;
use Bifrost\Enum\HttpStatusCode;
use PHPUnit\Framework\TestCase;

final class AppErrorTest extends TestCase
{
    public function testStoresHttpResponse(): void
    {
        $response = new HttpResponse(HttpStatusCode::BAD_REQUEST, 'bad request');
        $error = new AppError($response);

        self::assertSame($response, $error->response);
        self::assertSame('bad request', $error->getMessage());
    }
}
