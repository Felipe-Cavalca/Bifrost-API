<?php

declare(strict_types=1);

use Bifrost\Integration\Database\Driver\MysqlPdoDriver;
use Bifrost\Integration\Database\Driver\PgsqlPdoDriver;
use Bifrost\Integration\Database\Driver\SqlitePdoDriver;
use Bifrost\Interface\PdoDriverAdapter;
use PHPUnit\Framework\TestCase;

final class PdoDriverAdapterNamespaceTest extends TestCase
{
    public function testDriversImplementInterfaceNamespaceContract(): void
    {
        self::assertContains(PdoDriverAdapter::class, class_implements(MysqlPdoDriver::class));
        self::assertContains(PdoDriverAdapter::class, class_implements(PgsqlPdoDriver::class));
        self::assertContains(PdoDriverAdapter::class, class_implements(SqlitePdoDriver::class));
    }

    public function testLegacyDriverNamespaceAliasIsPreserved(): void
    {
        self::assertTrue(interface_exists(Bifrost\Integration\Database\Driver\PdoDriverAdapter::class));
        self::assertTrue(
            is_a(
                PdoDriverAdapter::class,
                Bifrost\Integration\Database\Driver\PdoDriverAdapter::class,
                true
            )
        );
    }
}
