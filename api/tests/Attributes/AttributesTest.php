<?php

declare(strict_types=1);

use Bifrost\Attributes\Cache;
use Bifrost\Attributes\Details;
use Bifrost\Attributes\Method;
use Bifrost\Attributes\OptionalFields;
use Bifrost\Attributes\OptionalParams;
use Bifrost\Attributes\RequiredFields;
use Bifrost\Attributes\RequiredParams;
use Bifrost\Attributes\Response;
use Bifrost\Class\HttpResponse;
use Bifrost\Core\Request;
use Bifrost\Enum\Field;
use Bifrost\Enum\HttpStatusCode;
use Bifrost\Interface\Controller;
use Bifrost\Interface\Responseable;
use PHPUnit\Framework\TestCase;

final class AttributesTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        bifrost_reset_get();
        bifrost_set_post_data([]);
        bifrost_reset_session();
    }

    public function testDetailsReturnsConfiguredOptions(): void
    {
        $attribute = new Details(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $attribute->getOptions());
    }

    public function testResponseFormatsNestedFields(): void
    {
        $attribute = new Response([
            'user' => [
                'id' => 'integer',
                'name' => 'string',
            ],
        ]);

        self::assertSame([
            'response' => [
                'user' => [
                    'id' => 'integer',
                    'name' => 'string',
                ],
            ],
        ], $attribute->getOptions());
    }

    public function testMethodBeforeAllowsConfiguredMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $attribute = new Method('GET', 'POST');

        self::assertNull($attribute->before());
        self::assertSame(['methods' => ['GET', 'POST']], $attribute->getOptions());
    }

    public function testMethodBeforeReturnsEndpointInformationOnOptions(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        bifrost_reset_get(['_controller' => 'index', '_action' => 'index']);

        $response = (new Method('GET'))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame(HttpStatusCode::OK, $response->status);
        self::assertSame('Endpoint information', $response->message);
        self::assertSame([
            'status' => 200,
            'message' => 'Endpoint information',
            'data' => ['attributes' => []],
            'errors' => null,
        ], $response->jsonSerialize());
    }

    public function testMethodBeforeRejectsInvalidMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $response = (new Method('GET'))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame(HttpStatusCode::METHOD_NOT_ALLOWED, $response->status);
    }

    public function testRequiredParamsValidatesPresenceAndType(): void
    {
        bifrost_reset_get(['page' => '12']);
        $attribute = new RequiredParams(['page' => Field::INT_IN_STRING]);

        self::assertNull($attribute->before());
        self::assertSame(['params' => ['page' => 'Integer in string']], $attribute->getOptions());
    }

    public function testRequiredParamsReturnsBadRequestWhenMissing(): void
    {
        bifrost_reset_get([]);
        $response = (new RequiredParams(['page' => Field::INT_IN_STRING]))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame(HttpStatusCode::BAD_REQUEST, $response->status);
        self::assertSame([
            'params' => ['page' => 'Field not found'],
        ], $response->jsonSerialize()['errors']);
    }

    public function testOptionalParamsRejectsInvalidType(): void
    {
        bifrost_reset_get(['page' => 'abc']);
        $response = (new OptionalParams(['page' => Field::INT_IN_STRING]))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame([
            'params' => ['page' => 'Invalid parameter type'],
        ], $response->jsonSerialize()['errors']);
    }

    public function testRequiredFieldsValidatesPresenceAndType(): void
    {
        bifrost_set_post_data(['name' => 'Alice']);
        $attribute = new RequiredFields(['name' => Field::STRING]);

        self::assertNull($attribute->before());
        self::assertSame(['fields' => ['name' => 'String']], $attribute->getOptions());
    }

    public function testRequiredFieldsReturnsBadRequestWhenTypeIsInvalid(): void
    {
        bifrost_set_post_data(['name' => 123]);
        $response = (new RequiredFields(['name' => Field::STRING]))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame([
            'fields' => ['name' => 'Invalid field type'],
        ], $response->jsonSerialize()['errors']);
    }

    public function testOptionalFieldsAllowsMissingFieldAndRejectsInvalidType(): void
    {
        bifrost_set_post_data([]);
        self::assertNull((new OptionalFields(['name' => Field::STRING]))->before());

        bifrost_set_post_data(['name' => 123]);
        $response = (new OptionalFields(['name' => Field::STRING]))->before();

        self::assertInstanceOf(HttpResponse::class, $response);
        self::assertSame([
            'fields' => ['name' => 'Invalid field type'],
        ], $response->jsonSerialize()['errors']);
    }

    public function testCacheAttributeExposesConfiguredTime(): void
    {
        bifrost_reset_session(['tenant' => 'acme']);
        $attribute = new Cache(60, ['tenant'], ['region' => 'br']);

        self::assertNull($attribute->before());
        $attribute->after(HttpResponse::success('cached'));

        self::assertSame(['cache' => ['seconds' => 60]], $attribute->getOptions());
    }

    public function testRequestCollectsAttributeOptions(): void
    {
        $controller = new #[AllowDynamicProperties] class implements Controller {
            #[Method('GET')]
            #[Details(['example' => true])]
            #[Response(['ok' => 'bool'])]
            public function index(): Responseable
            {
                return HttpResponse::success();
            }
        };

        $options = Request::getOptionsAttributes($controller, 'index');

        self::assertSame([
            'methods' => ['GET'],
            'example' => true,
            'response' => ['ok' => 'bool'],
        ], $options);
    }
}
