<?php

declare(strict_types=1);

use Bifrost\Core\SessionConfig;
use PHPUnit\Framework\TestCase;

final class SessionConfigTest extends TestCase
{
    public function testResolvesPlainFileSavePath(): void
    {
        $config = new SessionConfig(handler: 'files', savePath: '/tmp/sessions');

        self::assertTrue($config->shouldEnsureSavePath());
        self::assertSame('/tmp/sessions', $config->resolvedSavePath());
    }

    public function testResolvesFileSavePathWithSessionDepthPrefix(): void
    {
        $config = new SessionConfig(handler: 'files', savePath: '2;0600;/tmp/sessions');

        self::assertTrue($config->shouldEnsureSavePath());
        self::assertSame('/tmp/sessions', $config->resolvedSavePath());
    }

    public function testSkipsRemoteOrNonFileSessionHandlers(): void
    {
        $redis = new SessionConfig(handler: 'redis', savePath: 'tcp://redis:6379?prefix=bifrost:');
        $filesRemote = new SessionConfig(handler: 'files', savePath: 'tcp://redis:6379?prefix=bifrost:');

        self::assertFalse($redis->shouldEnsureSavePath());
        self::assertFalse($filesRemote->shouldEnsureSavePath());
        self::assertNull($filesRemote->resolvedSavePath());
    }
}
