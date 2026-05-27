<?php

declare(strict_types=1);

use Bifrost\Extension\DatabasePdo\PdoExtension;
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
}
