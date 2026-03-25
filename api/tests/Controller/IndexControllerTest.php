<?php

declare(strict_types=1);

use Bifrost\Controller\Index;
use Bifrost\Enum\HttpStatusCode;
use PHPUnit\Framework\TestCase;

final class IndexControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse(): void
    {
        $response = (new Index())->index();

        self::assertSame(HttpStatusCode::OK, $response->status);
        self::assertSame('Operation completed successfully', $response->message);
    }
}
