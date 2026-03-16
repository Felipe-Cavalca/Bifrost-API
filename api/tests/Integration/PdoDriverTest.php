<?php

declare(strict_types=1);

use Bifrost\Core\Database;
use Bifrost\Integration\Database\Driver\MysqlPdoDriver;
use Bifrost\Integration\Database\Driver\PgsqlPdoDriver;
use Bifrost\Integration\Database\Driver\SqlitePdoDriver;
use PHPUnit\Framework\TestCase;

final class PdoDriverTest extends TestCase
{
    protected function setUp(): void
    {
        bifrost_reset_database();
    }

    public function testSqliteDriverReadsSchemaData(): void
    {
        $driver = new SqlitePdoDriver();
        $database = new Database();

        self::assertContains('users', $driver->getTables($database, []));
        self::assertNotEmpty($driver->getDetTable($database, 'users'));
        self::assertFalse($driver->setSystemIdentifier($database->getConnection(), ['tenant' => 'acme']));
    }

    public function testMysqlAndPgsqlNormalizeFieldMetadata(): void
    {
        $mysql = new ReflectionMethod(MysqlPdoDriver::class, 'normalizeFields');
        $mysql->setAccessible(true);
        $pgsql = new ReflectionMethod(PgsqlPdoDriver::class, 'normalizeFields');
        $pgsql->setAccessible(true);

        $input = [[
            'Field' => 'id',
            'Type' => 'integer',
            'Null' => 'YES',
            'Default' => null,
            'Extra' => 'auto_increment',
            'pk' => 1,
        ]];

        self::assertSame([[
            'name' => 'id',
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'pk' => 1,
        ]], $mysql->invoke(new MysqlPdoDriver(), $input));

        self::assertSame([[
            'name' => 'id',
            'type' => 'integer',
            'null' => true,
            'default' => null,
            'pk' => 1,
        ]], $pgsql->invoke(new PgsqlPdoDriver(), $input));
    }
}
