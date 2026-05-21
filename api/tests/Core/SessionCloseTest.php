<?php

declare(strict_types=1);

use Bifrost\Core\Session;
use PHPUnit\Framework\TestCase;

final class SessionCloseTest extends TestCase
{
    protected function tearDown(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE && session_id() !== '') {
            session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        session_id('');
    }

    public function testCloseWritesAndClosesActiveSession(): void
    {
        $session = bifrost_reset_session(['tenant' => 'acme']);

        Session::close();

        self::assertSame(PHP_SESSION_NONE, session_status());

        session_start();
        self::assertSame('acme', $session->tenant);
    }
}
