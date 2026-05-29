<?php

declare(strict_types=1);

namespace Bifrost\DataTypes\Core\Tests;

use Bifrost\DataTypes\AbstractDataType;
use Bifrost\Framework\Contracts\DataType;
use Bifrost\Framework\Contracts\Insertable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AbstractDataTypeTest extends TestCase
{
    public function testCreatesValidDataType(): void
    {
        $value = CoreTestDataType::from(' ok ');

        self::assertInstanceOf(DataType::class, $value);
        self::assertInstanceOf(Insertable::class, $value);
        self::assertSame('ok', $value->value());
        self::assertSame('ok', (string) $value);
        self::assertSame('ok', $value->jsonSerialize());
    }

    public function testThrowsForInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CoreTestDataType::from('');
    }
}

final readonly class CoreTestDataType extends AbstractDataType
{
    public static function isValid(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    protected static function normalize(mixed $value): string
    {
        return trim((string) $value);
    }
}
