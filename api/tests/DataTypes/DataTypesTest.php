<?php

declare(strict_types=1);

use Bifrost\Core\AppError;
use Bifrost\DataTypes\Base64;
use Bifrost\DataTypes\DateTime;
use Bifrost\DataTypes\FilePath;
use Bifrost\DataTypes\StorageKey;
use Bifrost\DataTypes\Url;
use Bifrost\DataTypes\UUID;
use PHPUnit\Framework\TestCase;

final class DataTypesTest extends TestCase
{
    public function testUuidImplementsInsertableAndResponseable(): void
    {
        $uuid = new UUID('123e4567-e89b-12d3-a456-426614174000');

        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $uuid->value());
        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $uuid->jsonSerialize());
    }

    public function testUuidGenerateReturnsValidUuid(): void
    {
        $uuid = UUID::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
        self::assertInstanceOf(UUID::class, new UUID($uuid));
    }

    public function testOtherDataTypesValidateInput(): void
    {
        self::assertInstanceOf(Base64::class, new Base64(base64_encode('demo')));
        self::assertInstanceOf(DateTime::class, new DateTime(dateTime: '+15 minutes'));
        self::assertInstanceOf(FilePath::class, new FilePath('uploads/image.png'));
        self::assertInstanceOf(StorageKey::class, new StorageKey(key: 'uploads/image.png'));
        self::assertInstanceOf(Url::class, new Url('https://example.com'));
    }

    public function testStorageKeyNormalizesSlashAndRejectsUnsafeSegments(): void
    {
        self::assertSame('uploads/image.png', (new StorageKey(key: 'uploads\\image.png'))->value());

        $this->expectException(InvalidArgumentException::class);
        new StorageKey(key: '../image.png');
    }

    public function testDateTimeCanAssertFutureValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new DateTime(dateTime: '-1 minute'))->assertFuture();
    }

    public function testInvalidDatatypeThrowsAppError(): void
    {
        $this->expectException(AppError::class);
        new UUID('invalid');
    }
}
