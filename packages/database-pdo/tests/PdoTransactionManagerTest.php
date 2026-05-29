<?php

declare(strict_types=1);

use Bifrost\Extension\DatabasePdo\PdoExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\TransactionManager;
use PHPUnit\Framework\TestCase;

final class PdoTransactionManagerTest extends TestCase
{
    public function testRegistersPdoDatabaseAsTransactionManager(): void
    {
        $application = Application::create()->extend(new PdoExtension(['dsn' => 'sqlite::memory:']));
        $transactionManager = $application->container()->get(TransactionManager::class);

        self::assertInstanceOf(TransactionManager::class, $transactionManager);
        self::assertTrue($transactionManager->begin());
        self::assertTrue($transactionManager->rollback());
    }
}
