<?php

declare(strict_types=1);

use Bifrost\Core\Session;
use PHPUnit\Framework\TestCase;

final class SessionSavePathTest extends TestCase
{
    private string|false $originalHandler;
    private string $originalSavePath;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        $this->originalHandler = session_module_name();
        $this->originalSavePath = session_save_path();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        if (is_string($this->originalHandler)) {
            session_module_name($this->originalHandler);
        }

        session_save_path($this->originalSavePath);
    }

    public function testDoesNotCreateSavePathWhenSessionHandlerIsRedis(): void
    {
        @session_module_name('redis');

        if (session_module_name() !== 'redis') {
            self::markTestSkipped('Redis session handler is not available.');
        }

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bifrost-redis-session-' . bin2hex(random_bytes(4));
        session_save_path($path);

        $session = (new ReflectionClass(Session::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(Session::class, 'ensureSavePath');
        $method->setAccessible(true);
        $method->invoke($session);

        self::assertDirectoryDoesNotExist($path);
    }
}
