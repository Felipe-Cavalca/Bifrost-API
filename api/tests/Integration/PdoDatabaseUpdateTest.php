<?php

declare(strict_types=1);

use Bifrost\Core\Database;
use PHPUnit\Framework\TestCase;

final class PdoDatabaseUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        bifrost_reset_database();
    }

    public function testUpdateWritesNullAndBooleanValues(): void
    {
        $database = new Database();

        self::assertTrue($database->update(
            table: 'users',
            data: [
                'email' => null,
                'active' => false,
            ],
            where: ['name' => 'Alice']
        ));

        $rows = $database->select(
            table: 'users',
            fields: ['email', 'active'],
            where: ['name' => 'Alice']
        );

        self::assertNull($rows[0]['email']);
        self::assertSame(0, (int) $rows[0]['active']);
    }
}
