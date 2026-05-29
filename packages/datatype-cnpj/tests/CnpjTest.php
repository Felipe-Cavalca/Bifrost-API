<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Cnpj\Tests;

use Bifrost\DataTypes\Brazil\Cnpj;
use PHPUnit\Framework\TestCase;

final class CnpjTest extends TestCase
{
    public function testValidatesCnpj(): void
    {
        self::assertSame('11222333000181', Cnpj::from('11.222.333/0001-81')->value());
        self::assertFalse(Cnpj::isValid('11.111.111/1111-11'));
    }
}
