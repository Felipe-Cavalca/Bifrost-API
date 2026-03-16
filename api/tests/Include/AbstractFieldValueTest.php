<?php

declare(strict_types=1);

use Bifrost\Core\AppError;
use Bifrost\Enum\Field;
use Bifrost\Include\AbstractFieldValue;
use PHPUnit\Framework\TestCase;

final class AbstractFieldValueTest extends TestCase
{
    public function testInitStoresValidatedValue(): void
    {
        $object = new class {
            use AbstractFieldValue;

            public function expose(mixed $value, Field $field): mixed
            {
                return $this->init($value, $field);
            }

            public function value(): mixed
            {
                return $this->value;
            }
        };

        $object->expose('https://example.com', Field::URL);

        self::assertSame('https://example.com', $object->value());
    }

    public function testInitThrowsOnInvalidValue(): void
    {
        $object = new class {
            use AbstractFieldValue;

            public function expose(mixed $value, Field $field): mixed
            {
                return $this->init($value, $field);
            }
        };

        $this->expectException(AppError::class);
        $object->expose('invalid', Field::UUID);
    }
}
