<?php

declare(strict_types=1);

use Bifrost\Core\Settings;
use PHPUnit\Framework\TestCase;

final class SettingsEnvTest extends TestCase
{
    public function testGetEnvPreservesZeroString(): void
    {
        putenv('BFR_API_TEST_ZERO=0');

        $settings = new Settings();

        self::assertSame('0', $settings->BFR_API_TEST_ZERO);

        putenv('BFR_API_TEST_ZERO');
    }
}
