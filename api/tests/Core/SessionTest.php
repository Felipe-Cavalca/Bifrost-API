<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    private string $originalSavePath;

    protected function setUp(): void
    {
        $this->originalSavePath = session_save_path();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        session_save_path($this->originalSavePath);
    }

    public function testStoresReadsAndDestroysSessionData(): void
    {
        $session = bifrost_reset_session(['tenant' => 'acme']);

        self::assertSame('acme', $session->tenant);
        self::assertTrue(isset($session->tenant));
        self::assertStringContainsString('"tenant":"acme"', (string) $session);

        $session->user = 'alice';
        unset($session->tenant);

        self::assertFalse(isset($session->tenant));
        self::assertSame('alice', $session->user);

        $session->destroy();
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testCreatesFileSessionSavePathWhenMissing(): void
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bifrost-session-' . bin2hex(random_bytes(4));

        session_save_path($path);

        $session = new Bifrost\Core\Session();

        self::assertDirectoryExists($path);

        $session->destroy();
        rmdir($path);
    }
}
