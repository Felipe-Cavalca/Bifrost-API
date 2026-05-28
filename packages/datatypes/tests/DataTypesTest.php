<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Tests;

use Bifrost\DataTypes\Base64;
use Bifrost\DataTypes\Brazil\Cnpj;
use Bifrost\DataTypes\Brazil\Cpf;
use Bifrost\DataTypes\Email;
use Bifrost\DataTypes\Filesystem\FilePath;
use Bifrost\DataTypes\Storage\StorageKey;
use Bifrost\DataTypes\Url;
use Bifrost\DataTypes\Uuid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DataTypesTest extends TestCase
{
    public function testValidatesCommonDataTypes(): void
    {
        self::assertSame('team@bifrost.dev', Email::from('TEAM@BIFROST.DEV')->value());
        self::assertTrue(Url::isValid('https://example.com/docs'));
        self::assertTrue(Base64::isValid(base64_encode('bifrost')));
        self::assertTrue(Uuid::isValid(Uuid::generate()->value()));
    }

    public function testValidatesBrazilianDocuments(): void
    {
        self::assertSame('52998224725', Cpf::from('529.982.247-25')->value());
        self::assertSame('11222333000181', Cnpj::from('11.222.333/0001-81')->value());
    }

    public function testValidatesFilesystemDataTypes(): void
    {
        self::assertTrue(FilePath::isValid('avatars/user.png'));
        self::assertTrue(StorageKey::isValid('documents/report.pdf'));
        self::assertFalse(FilePath::isValid('../secret.txt'));
    }

    public function testThrowsWhenValueIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Email::from('invalid');
    }
}
