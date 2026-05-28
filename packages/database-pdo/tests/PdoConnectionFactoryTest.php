<?php

declare(strict_types=1);

use Bifrost\Extension\DatabasePdo\PdoExtension;
use Bifrost\Extension\DatabasePdo\PdoDatabase;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use PHPUnit\Framework\TestCase;

final class PdoConnectionFactoryTest extends TestCase
{
    public function testCreatesReusablePdoConnection(): void
    {
        $application = Application::create()->extend(new PdoExtension(['dsn' => 'sqlite::memory:']));
        $factory = $application->container()->get(DatabaseConnectionFactory::class);

        self::assertSame($factory->connection(), $factory->connection());
        self::assertSame(1, (int) $factory->connection()->query('SELECT 1')->fetchColumn());
    }

    public function testPdoDatabaseExecutesCrudQueries(): void
    {
        $application = Application::create()->extend(new PdoExtension(['dsn' => 'sqlite::memory:']));
        $database = $application->container()->get(PdoDatabase::class);

        $database->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, active INTEGER NOT NULL)');

        $id = $database->insert('users', ['name' => 'Ana', 'active' => 1]);

        self::assertSame('1', $id);
        self::assertTrue($database->exists('users', ['name' => 'Ana']));
        self::assertSame([['id' => 1, 'name' => 'Ana']], $database->select('users', ['id', 'name'], ['active' => 1]));
        self::assertSame(1, $database->update('users', ['name' => 'Maria'], ['id' => 1]));
        self::assertSame('Maria', $database->value('SELECT name FROM users WHERE id = :id', ['id' => 1]));
        self::assertSame(1, $database->delete('users', ['id' => 1]));
        self::assertFalse($database->exists('users', ['id' => 1]));
    }
}
