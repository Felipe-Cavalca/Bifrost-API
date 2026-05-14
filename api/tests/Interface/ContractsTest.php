<?php

declare(strict_types=1);

use Bifrost\Attributes\Cache as CacheAttribute;
use Bifrost\Attributes\Method;
use Bifrost\Controller\Index;
use Bifrost\Core\Cache;
use Bifrost\Core\Database;
use Bifrost\Core\Queue;
use Bifrost\DataTypes\UUID;
use Bifrost\Integration\Database\MongoDatabase;
use Bifrost\Integration\S3Storage;
use PHPUnit\Framework\TestCase;

final class ContractsTest extends TestCase
{
    public function testInterfacesAreImplementedByCurrentConcreteTypes(): void
    {
        self::assertContains(Bifrost\Interface\Attribute::class, class_implements(Method::class));
        self::assertContains(Bifrost\Interface\AttributeBefore::class, class_implements(Method::class));
        self::assertContains(Bifrost\Interface\AttributeAfter::class, class_implements(CacheAttribute::class));
        self::assertContains(Bifrost\Interface\Controller::class, class_implements(Index::class));
        self::assertContains(Bifrost\Interface\Cache::class, class_implements(Cache::class));
        self::assertContains(Bifrost\Interface\Database::class, class_implements(Database::class));
        self::assertContains(Bifrost\Interface\Queue::class, class_implements(Queue::class));
        self::assertContains(Bifrost\Interface\Insertable::class, class_implements(UUID::class));
        self::assertContains(Bifrost\Interface\Responseable::class, class_implements(UUID::class));
        self::assertContains(Bifrost\Interface\Storage::class, class_implements(S3Storage::class));
        self::assertContains(Bifrost\Interface\NoSqlDatabase::class, class_implements(MongoDatabase::class));
    }
}
