<?php

declare(strict_types=1);

use App\DataTypes\ProjectCode;
use Bifrost\Framework\Contracts\Responseable;
use PHPUnit\Framework\TestCase;

final class ResponseableDataTypeTest extends TestCase
{
    public function testProjectCodeCanBeReturnedDirectlyByAController(): void
    {
        $projectCode = ProjectCode::from('app-1234');

        self::assertInstanceOf(Responseable::class, $projectCode);
        self::assertSame('APP-1234', $projectCode->jsonSerialize());
    }
}
