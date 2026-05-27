<?php

declare(strict_types=1);

use Bifrost\Extension\DatabasePostgreSql\PostgreSqlExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use PHPUnit\Framework\TestCase;

final class PostgreSqlExtensionTest extends TestCase
{
    public function testConnectsUsingPostgreSqlExtension(): void
    {
        $application = Application::create()->extend(new PostgreSqlExtension([
            'host' => getenv('POSTGRES_HOST') ?: 'postgres',
            'port' => (int) (getenv('POSTGRES_PORT') ?: 5432),
            'database' => 'bifrost',
            'username' => 'bifrost',
            'password' => 'bifrost',
        ]));
        $factory = $application->container()->get(DatabaseConnectionFactory::class);

        self::assertSame(1, (int) $factory->connection()->query('SELECT 1')->fetchColumn());
    }
}
