<?php

declare(strict_types=1);

use Bifrost\Core\Get;
use PHPUnit\Framework\TestCase;

final class GetPathActionFallbackTest extends TestCase
{
    public function testUnmappedControllerPathCanCarryAction(): void
    {
        $get = bifrost_reset_get(['_controller' => 'documents/presignUpload']);

        self::assertSame('documents', $get->controller);
        self::assertSame('presignUpload', $get->action);
        self::assertFalse(Get::$routeMapped);
    }

    public function testExplicitActionKeepsControllerPathUnchanged(): void
    {
        $get = bifrost_reset_get([
            '_controller' => 'documents/presignUpload',
            '_action' => 'index',
        ]);

        self::assertSame('documents/presignUpload', $get->controller);
        self::assertSame('index', $get->action);
        self::assertFalse(Get::$routeMapped);
    }
}
