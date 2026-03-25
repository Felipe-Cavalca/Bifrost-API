<?php

declare(strict_types=1);

use Bifrost\Enum\Field;
use Bifrost\Enum\HttpStatusCode;
use Bifrost\Enum\Path;
use Bifrost\Enum\Routes;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function testHttpStatusCodeHelpers(): void
    {
        self::assertSame('OK', HttpStatusCode::OK->message());
        self::assertTrue(HttpStatusCode::CREATED->isSuccess());
        self::assertTrue(HttpStatusCode::FOUND->isRedirection());
        self::assertTrue(HttpStatusCode::BAD_REQUEST->isClientError());
        self::assertTrue(HttpStatusCode::INTERNAL_SERVER_ERROR->isServerError());
    }

    public function testFieldValidationHelpers(): void
    {
        self::assertTrue(Field::INT->validate(1));
        self::assertTrue(Field::INT_IN_STRING->validate('10'));
        self::assertTrue(Field::STRING->validate('x'));
        self::assertTrue(Field::FLOAT->validate(1.5));
        self::assertTrue(Field::BOOL->validate(true));
        self::assertTrue(Field::ARRAY->validate(['x']));
        self::assertTrue(Field::OBJECT->validate((object) ['x' => 1]));
        self::assertTrue(Field::NULL->validate(null));
        self::assertTrue(Field::CPF->validate('52998224725'));
        self::assertTrue(Field::CNPJ->validate('19131243000197'));
        self::assertTrue(Field::EMAIL->validate('user@example.com'));
        self::assertTrue(Field::URL->validate('https://example.com'));
        self::assertTrue(Field::BASE64->validate(base64_encode('abc')));
        self::assertTrue(Field::JSON->validate('{"a":1}'));
        self::assertTrue(Field::UUID->validate('123e4567-e89b-12d3-a456-426614174000'));
        self::assertTrue(Field::FOLDER_NAME->validate('uploads'));
        self::assertTrue(Field::FOLDER_PATH->validate('/var/tmp/uploads'));
        self::assertTrue(Field::FILE_NAME->validate('image.png'));
        self::assertTrue(Field::FILE_PATH->validate('uploads/image.png'));
        self::assertFalse(Field::DEFAULT->validate('x'));
    }

    public function testPathAndRoutesHelpers(): void
    {
        self::assertSame('./', Path::FOLDER->toDirectory());
        self::assertSame(Routes::login, Routes::fromRequest('login'));
        self::assertSame(Routes::logout, Routes::fromRequest('logout'));
        self::assertNull(Routes::fromRequest('missing-route'));
    }
}
