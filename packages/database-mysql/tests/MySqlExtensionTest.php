<?php

declare(strict_types=1);

use Bifrost\Extension\DatabaseMySql\MySqlExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\DatabaseConnectionFactory;
use PHPUnit\Framework\TestCase;

final class MySqlExtensionTest extends TestCase
{
    public function testConnectsUsingMySqlExtension(): void
    {
        $application = Application::create()->extend(new MySqlExtension([
            'host' => getenv('MYSQL_HOST') ?: 'mysql',
            'port' => (int) (getenv('MYSQL_PORT') ?: 3306),
            'database' => 'bifrost',
            'username' => 'bifrost',
            'password' => 'bifrost',
        ]));
        $factory = $application->container()->get(DatabaseConnectionFactory::class);

        self::assertSame(1, (int) $factory->connection()->query('SELECT 1')->fetchColumn());
    }
}
