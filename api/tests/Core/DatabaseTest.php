<?php

declare(strict_types=1);

use Bifrost\Core\AppError;
use Bifrost\Core\Database;
use Bifrost\DataTypes\UUID;
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        bifrost_reset_database();
    }

    private function invokePrivateStaticMethod(string $methodName, array $args = []): mixed
    {
        $reflector = new ReflectionMethod(Database::class, $methodName);
        $reflector->setAccessible(true);
        return $reflector->invokeArgs(null, $args);
    }

    public function testBuildQueryHelpers(): void
    {
        self::assertSame('SELECT * FROM users', $this->invokePrivateStaticMethod('buildSelectQuery', ['users', '*']));
        self::assertSame('SELECT u.id, name AS username FROM users', $this->invokePrivateStaticMethod('buildSelectQuery', ['users', ['u.id', 'name' => 'username']]));
        self::assertSame(
            "INSERT INTO users (name, active, uuid) VALUES ('John', 1, '123e4567-e89b-12d3-a456-426614174000')",
            $this->invokePrivateStaticMethod('buildInsertQuery', ['users', [
                'name' => 'John',
                'active' => 1,
                'uuid' => new UUID('123e4567-e89b-12d3-a456-426614174000'),
            ]])
        );
        self::assertSame("UPDATE users SET name = 'John', active = 1", $this->invokePrivateStaticMethod('buildUpdateQuery', ['users', ['name' => 'John', 'active' => 1]]));
        self::assertSame('DELETE FROM users', $this->invokePrivateStaticMethod('buildDeleteQuery', ['users']));
        self::assertSame("id = 1 AND deleted_at IS NULL AND name IN ('Alice', 'Bob')", $this->invokePrivateStaticMethod('buildWhereQuery', [[
            'id' => 1,
            'deleted_at' => null,
            'name' => ['Alice', 'Bob'],
        ]]));
        self::assertSame("(id = 1 OR name = 'Alice')", $this->invokePrivateStaticMethod('buildWhereQuery', [['OR' => ['id' => 1, 'name' => 'Alice']]]));
        self::assertSame('INNER JOIN logs l ON l.user_id = users.id', $this->invokePrivateStaticMethod('buildJoinQuery', [['INNER JOIN logs l ON l.user_id = users.id']]));
    }

    public function testDatabaseCrudAndMetadata(): void
    {
        $database = new Database();

        self::assertSame('sqlite', $database->getDriver());
        self::assertFalse($database->hasReturning());
        self::assertTrue($database->existTable('users'));
        self::assertTrue($database->existField('users', 'email'));
        self::assertNotEmpty($database->getTables());
        self::assertNotEmpty($database->getDetTable('users'));

        $id = $database->insert('users', ['name' => 'Carol', 'email' => 'carol@example.com', 'active' => 1, 'uuid' => '123e4567-e89b-12d3-a456-426614174002']);
        self::assertNotFalse($id);

        $rows = $database->select('users', ['id', 'name'], where: ['name' => 'Carol']);
        self::assertCount(1, $rows);
        self::assertSame('Carol', $rows[0]['name']);

        self::assertTrue((bool) $database->update('users', ['name' => 'Caroline'], ['id' => (int) $id]));
        self::assertTrue((bool) $database->exists('users', ['name' => 'Caroline']));
        self::assertSame('Caroline', $database->query(select: ['name'], from: 'users', where: ['id' => (int) $id], returnFirst: true)['name']);

        self::assertTrue((bool) $database->delete('users', ['id' => (int) $id]));
        self::assertFalse((bool) $database->exists('users', ['id' => (int) $id]));
        self::assertSame([], $database->getDetTable('missing'));
        self::assertFalse($database->setSystemIdentifier(['tenant' => 'acme']));
    }

    public function testDatabaseQueryFacadeVariants(): void
    {
        $database = new Database();

        self::assertIsArray($database->query(select: ['id', 'name'], from: 'users', order: 'id ASC'));
        self::assertTrue((bool) $database->query(update: 'users', set: ['active' => 1], where: ['name' => 'Bob']));
        self::assertTrue((bool) $database->query(delete: 'users', where: ['name' => 'Bob']));
        self::assertTrue((bool) $database->query(from: 'users', where: ['name' => 'Alice'], exists: true));
        self::assertSame('Alice', $database->query(query: 'SELECT name FROM users WHERE name = :name', params: ['name' => 'Alice'], returnFirst: true)['name']);
    }

    public function testExecuteQueryWrapsPdoExceptions(): void
    {
        $database = new Database();

        $this->expectException(AppError::class);
        $database->executeQuery('SELECT * FROM missing_table');
    }
}
