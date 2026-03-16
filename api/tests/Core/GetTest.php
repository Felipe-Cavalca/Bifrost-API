<?php

declare(strict_types=1);

use Bifrost\Core\Get;
use PHPUnit\Framework\TestCase;

final class GetTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testMapsControllerActionAndQueryData(): void
    {
        $get = bifrost_reset_get([
            '_controller' => 'login',
            '_action' => 'ignored',
            'page' => '2',
        ]);

        self::assertSame('auth', $get->controller);
        self::assertSame('login', $get->action);
        self::assertSame('2', $get->page);
        self::assertStringContainsString('"page": "2"', (string) $get);
    }

    public function testAllowsMutationAndUnset(): void
    {
        $get = bifrost_reset_get(['foo' => 'bar']);

        $get->controller = 'users';
        $get->action = 'show';
        $get->page = '3';
        unset($get->foo);

        self::assertSame('users', $get->controller);
        self::assertSame('show', $get->action);
        self::assertSame('3', $get->page);
        self::assertFalse(isset($get->foo));
    }
}
