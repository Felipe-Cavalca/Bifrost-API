<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Cpf\Tests;

use Bifrost\DataTypes\Brazil\Cpf;
use PHPUnit\Framework\TestCase;

final class CpfTest extends TestCase
{
    public function testValidatesCpf(): void
    {
        self::assertSame('52998224725', Cpf::from('529.982.247-25')->value());
        self::assertFalse(Cpf::isValid('111.111.111-11'));
    }
}
