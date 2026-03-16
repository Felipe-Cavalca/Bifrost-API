<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
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
}
