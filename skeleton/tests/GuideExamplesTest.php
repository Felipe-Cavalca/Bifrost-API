<?php

declare(strict_types=1);

use App\Attributes\Permission;
use App\DataTypes\ProjectCode;
use Bifrost\Framework\Container;
use Bifrost\Framework\Http\Request;
use PHPUnit\Framework\TestCase;

final class GuideExamplesTest extends TestCase
{
    public function testPermissionAllowsExpectedHeader(): void
    {
        $attribute = new Permission('documents.read');
        $request = new Request('GET', '/', headers: ['X-App-Permission' => 'documents.read']);

        self::assertNull($attribute->before($request, new Container()));
        self::assertSame(['permission' => 'documents.read'], $attribute->options());
    }

    public function testPermissionRejectsMissingHeader(): void
    {
        $response = (new Permission('documents.read'))
            ->before(new Request('GET', '/'), new Container());

        self::assertNotNull($response);
        self::assertSame(403, $response->status());
    }

    public function testProjectCodeNormalizesInsertableValue(): void
    {
        self::assertSame('APP-1234', ProjectCode::from('app-1234')->value());
    }

    public function testProjectCodeRejectsInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ProjectCode::from('invalid');
    }
}
