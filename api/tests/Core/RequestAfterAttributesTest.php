<?php

declare(strict_types=1);

use Bifrost\Class\HttpResponse;
use Bifrost\Core\Request;
use Bifrost\Interface\Attribute as InterfaceAttribute;
use Bifrost\Interface\AttributeAfter;
use Bifrost\Interface\AttributeBefore;
use Bifrost\Interface\Controller;
use Bifrost\Interface\Responseable;
use PHPUnit\Framework\TestCase;

final class RequestAfterAttributesTest extends TestCase
{
    protected function tearDown(): void
    {
        RequestAfterProbeAttribute::$calls = [];
    }

    public function testAfterAttributesRunWhenBeforeAttributeReturnsResponse(): void
    {
        $controller = new class implements Controller {
            #[RequestAfterProbeAttribute('before-response')]
            public function index(): Responseable
            {
                return HttpResponse::success('should not run');
            }
        };

        Request::run(controller: $controller, action: 'index');

        self::assertSame(['before-response:blocked'], RequestAfterProbeAttribute::$calls);
    }

    public function testAfterAttributesRunWhenActionThrows(): void
    {
        $controller = new class implements Controller {
            #[RequestAfterProbeAttribute('throwable')]
            public function index(): Responseable
            {
                throw new RuntimeException('boom');
            }
        };

        Request::run(controller: $controller, action: 'index');

        self::assertSame(['throwable:boom'], RequestAfterProbeAttribute::$calls);
    }

    public function testAfterAttributeFailureIsNotExecutedAgain(): void
    {
        $controller = new class implements Controller {
            #[RequestAfterProbeAttribute('after-failure')]
            public function index(): Responseable
            {
                return HttpResponse::success('ok');
            }
        };

        $response = Request::run(controller: $controller, action: 'index');

        self::assertSame(500, $response->status->value);
        self::assertSame('After attribute failed', $response->message);
        self::assertSame(['after-failure:ok'], RequestAfterProbeAttribute::$calls);
    }
}

#[\Attribute]
final class RequestAfterProbeAttribute implements InterfaceAttribute, AttributeBefore, AttributeAfter
{
    public static array $calls = [];

    private string $label;

    public function __construct(...$params)
    {
        $this->label = (string) ($params[0] ?? '');
    }

    public function before(): ?Responseable
    {
        if ($this->label === 'before-response') {
            return HttpResponse::badRequest(errors: [], message: 'blocked');
        }

        return null;
    }

    public function after(Responseable $response): void
    {
        if ($response instanceof HttpResponse) {
            self::$calls[] = $this->label . ':' . $response->message;
        }

        if ($this->label === 'after-failure') {
            throw new RuntimeException('after failed');
        }
    }

    public function getOptions(): array
    {
        return [];
    }
}
