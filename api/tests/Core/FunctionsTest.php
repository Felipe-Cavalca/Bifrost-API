<?php

declare(strict_types=1);

use Bifrost\Core\Functions;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    public function testSanitizeEscapesStrings(): void
    {
        self::assertSame("\\'", Functions::sanitize("'"));
        self::assertSame("\\\\", Functions::sanitize("\\"));
        self::assertSame('hello', Functions::sanitize('hello'));
        self::assertSame('123', Functions::sanitize(123));
    }

    public function testSanitizeArrayEscapesOnlyStrings(): void
    {
        self::assertSame(
            ["O\\'Reilly", 123, null, '\"quoted\"'],
            Functions::sanitizeArray(["O'Reilly", 123, null, '"quoted"'])
        );
    }
}
