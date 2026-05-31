<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Tests;

use Bifrost\DataTypes\Base64;
use Bifrost\DataTypes\Brazil\Cnpj;
use Bifrost\DataTypes\Brazil\Cpf;
use Bifrost\DataTypes\DateTime;
use Bifrost\DataTypes\Email;
use Bifrost\DataTypes\Filesystem\FileName;
use Bifrost\DataTypes\Filesystem\FilePath;
use Bifrost\DataTypes\Filesystem\FolderName;
use Bifrost\DataTypes\Filesystem\FolderPath;
use Bifrost\DataTypes\Json;
use Bifrost\DataTypes\Storage\StorageKey;
use Bifrost\DataTypes\Url;
use Bifrost\DataTypes\Uuid;
use Bifrost\Framework\Contracts\Insertable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DataTypesTest extends TestCase
{
    public function testValidatesCommonDataTypes(): void
    {
        self::assertSame('team@bifrost.dev', Email::from('TEAM@BIFROST.DEV')->value());
        self::assertTrue(Url::isValid('https://example.com/docs'));
        self::assertTrue(Base64::isValid(base64_encode('bifrost')));
        self::assertTrue(Json::isValid('{"name":"bifrost"}'));
        self::assertTrue(Uuid::isValid(Uuid::generate()->value()));
    }

    public function testValidatesDateTimeDataType(): void
    {
        self::assertSame(
            '2026-05-27 10:30:00',
            DateTime::from('2026-05-27 10:30:00')->value()
        );
        self::assertFalse(DateTime::isValid(''));
        self::assertFalse(DateTime::isValid('not a date'));
    }

    public function testValidatesBrazilianDocuments(): void
    {
        self::assertSame('52998224725', Cpf::from('529.982.247-25')->value());
        self::assertSame('11222333000181', Cnpj::from('11.222.333/0001-81')->value());
    }

    public function testValidatesFilesystemDataTypes(): void
    {
        self::assertTrue(FilePath::isValid('avatars/user.png'));
        self::assertTrue(FileName::isValid('user.png'));
        self::assertTrue(FolderName::isValid('avatars'));
        self::assertTrue(FolderPath::isValid('/var/uploads'));
        self::assertTrue(StorageKey::isValid('documents/report.pdf'));
        self::assertSame('documents/report.pdf', StorageKey::from('documents\\report.pdf')->value());

        self::assertFalse(FileName::isValid('.env'));
        self::assertFalse(FileName::isValid('user/name.png'));
        self::assertFalse(FolderName::isValid('some.folder'));
        self::assertFalse(FolderPath::isValid('/var//uploads'));
        self::assertFalse(FilePath::isValid('../secret.txt'));
        self::assertFalse(FilePath::isValid('/secret.txt'));
        self::assertFalse(StorageKey::isValid('/documents/report.pdf'));
    }

    public function testThrowsWhenValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::from('invalid');
    }

    public function testDataTypesAreInsertable(): void
    {
        $email = Email::from('team@bifrost.dev');

        self::assertInstanceOf(Insertable::class, $email);
        self::assertSame('team@bifrost.dev', $email->value());
    }
}
