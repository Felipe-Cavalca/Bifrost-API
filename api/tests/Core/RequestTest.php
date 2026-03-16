<?php

declare(strict_types=1);

use Bifrost\Attributes\Details;
use Bifrost\Attributes\Method;
use Bifrost\Class\HttpResponse;
use Bifrost\Core\Request;
use Bifrost\Enum\HttpStatusCode;
use Bifrost\Interface\Controller;
use Bifrost\Interface\Responseable;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        bifrost_reset_get(['_controller' => 'index', '_action' => 'index']);
    }

    public function testRunWithControllerInstanceExecutesAction(): void
    {
        $controller = new class implements Controller {
            public function index(): Responseable
            {
                return HttpResponse::success('ok');
            }
        };

        $response = Request::run($controller, 'index');

        self::assertSame(HttpStatusCode::OK, $response->status);
        self::assertSame('ok', $response->message);
    }

    public function testRunReturnsNotFoundForMissingController(): void
    {
        $response = Request::run('missing', 'index');

        self::assertSame(HttpStatusCode::NOT_FOUND, $response->status);
        self::assertSame('Controller not found', $response->message);
    }

    public function testRunReturnsNotFoundForMissingAction(): void
    {
        $controller = new class implements Controller {
            public function index(): Responseable
            {
                return HttpResponse::success();
            }
        };

        $response = Request::run($controller, 'missing');

        self::assertSame(HttpStatusCode::NOT_FOUND, $response->status);
        self::assertSame('Action not found', $response->message);
    }

    public function testRunHonorsBeforeAttributeResponse(): void
    {
        $controller = new class implements Controller {
            #[Method('POST')]
            public function index(): Responseable
            {
                return HttpResponse::success('should not run');
            }
        };

        $response = Request::run($controller, 'index');

        self::assertSame(HttpStatusCode::METHOD_NOT_ALLOWED, $response->status);
    }

    public function testStringCastUsesJsonEncoding(): void
    {
        $request = new Request();

        self::assertJsonStringEqualsJsonString(
            json_encode(Request::run('index', 'index')),
            (string) $request
        );
    }

    public function testGetOptionsAttributesReturnsAttributeMetadata(): void
    {
        $controller = new class implements Controller {
            #[Method('GET')]
            #[Details(['description' => 'demo'])]
            public function index(): Responseable
            {
                return HttpResponse::success();
            }
        };

        self::assertSame([
            'methods' => ['GET'],
            'description' => 'demo',
        ], Request::getOptionsAttributes($controller, 'index'));
    }

    public function testUnhandledThrowableBecomesInternalServerError(): void
    {
        $controller = new class implements Controller {
            public function index(): Responseable
            {
                throw new RuntimeException('boom');
            }
        };

        $response = Request::run($controller, 'index');

        self::assertSame(HttpStatusCode::INTERNAL_SERVER_ERROR, $response->status);
        self::assertSame('boom', $response->message);
    }
}
