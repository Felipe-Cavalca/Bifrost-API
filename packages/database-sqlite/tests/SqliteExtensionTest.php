<?php

declare(strict_types=1);

use Bifrost\Extension\DatabaseSqlite\SqliteExtension;
use Bifrost\Extension\DatabasePdo\PdoDatabase;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use PHPUnit\Framework\TestCase;

final class SqliteExtensionTest extends TestCase
{
    public function testConnectsUsingMemoryDatabase(): void
    {
        $application = Application::create()->extend(new SqliteExtension(['memory' => true]));
        $factory = $application->container()->get(DatabaseConnectionFactory::class);

        self::assertSame('sqlite', $factory->connection()->getAttribute(PDO::ATTR_DRIVER_NAME));
        self::assertSame(1, (int) $factory->connection()->query('SELECT 1')->fetchColumn());
    }

    public function testConnectsUsingNamedConnections(): void
    {
        $application = Application::create()->extend(new SqliteExtension([
            'connections' => [
                'default' => ['memory' => true],
                'analytics' => ['memory' => true],
            ],
        ]));
        $factory = $application->container()->get(DatabaseConnectionFactory::class);

        self::assertNotSame($factory->connection(), $factory->connection('analytics'));
        self::assertSame('sqlite', $factory->connection('analytics')->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    public function testRegistersPdoDatabaseHelper(): void
    {
        $application = Application::create()->extend(new SqliteExtension(['memory' => true]));
        $database = $application->container()->get(PdoDatabase::class);

        $database->execute('CREATE TABLE events (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
        $database->insert('events', ['name' => 'created']);

        self::assertSame('created', $database->value('SELECT name FROM events WHERE id = :id', ['id' => 1]));
    }
}
