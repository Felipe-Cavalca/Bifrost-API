<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Attribute;
use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\AfterResponseAttribute;
use Bifrost\Framework\Contracts\Responseable;
use Bifrost\Framework\Http\Request;
use Bifrost\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

final class ResponseableTest extends TestCase
{
    public function testReturnsResponseableObjectAsJson(): void
    {
        $application = Application::create();
        $application->get('/user', fn (): Responseable => new ResponseableUser('user-123', 'Ada'));

        $response = $application->handle(new Request(method: 'GET', path: '/user'));

        self::assertSame(200, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertJsonStringEqualsJsonString('{"id":"user-123","name":"Ada"}', $response->body());
    }

    public function testReturnsResponseableScalarAsJson(): void
    {
        $application = Application::create();
        $application->get('/code', fn (): Responseable => new ResponseableCode('APP-1234'));

        $response = $application->handle(new Request(method: 'GET', path: '/code'));

        self::assertSame('"APP-1234"', $response->body());
    }

    public function testConvertsResponseableBeforeRunningAfterResponseAttribute(): void
    {
        $application = Application::create();
        $application->get('/user', [ResponseableController::class, 'show']);

        $response = $application->handle(new Request(method: 'GET', path: '/user'));

        self::assertSame('yes', $response->headers()['X-Responseable']);
        self::assertJsonStringEqualsJsonString('{"id":"user-123","name":"Ada"}', $response->body());
    }
}

final readonly class ResponseableUser implements Responseable
{
    public function __construct(private string $id, private string $name)
    {
    }

    /**
     * @return array{id: string, name: string}
     */
    public function jsonSerialize(): array
    {
        return ['id' => $this->id, 'name' => $this->name];
    }
}

final readonly class ResponseableCode implements Responseable
{
    public function __construct(private string $value)
    {
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final class ResponseableController
{
    #[MarkResponseable]
    public function show(): Responseable
    {
        return new ResponseableUser('user-123', 'Ada');
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
final class MarkResponseable implements AfterResponseAttribute
{
    public function after(Request $request, Response $response, Container $container): ?Response
    {
        return $response->withHeader('X-Responseable', 'yes');
    }

    public function options(): array
    {
        return [];
    }
}
