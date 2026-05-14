<?php

declare(strict_types=1);

use Bifrost\Class\HttpResponse;
use Bifrost\Core\Logger;
use Bifrost\Core\Request;
use Bifrost\DataTypes\UUID;
use Bifrost\Interface\Controller;
use PHPUnit\Framework\TestCase;

final class RequestObservabilityInfoTest extends TestCase
{
    private ?string $logFile = null;

    protected function tearDown(): void
    {
        putenv('BFR_API_LOG_DRIVER=none');
        putenv('BFR_API_LOG_FILE');
        Logger::resetRequestId();

        if ($this->logFile !== null && is_file($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public function testRequestIdIsAddedToHttpResponseWhenLoggerIsEnabled(): void
    {
        $this->logFile = tempnam(sys_get_temp_dir(), 'bifrost-request-log-');
        putenv('BFR_API_LOG_DRIVER=file');
        putenv('BFR_API_LOG_FILE=' . $this->logFile);
        Logger::resetRequestId(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $response = Request::run(
            controller: new class implements Controller {
                public function index(): HttpResponse
                {
                    return HttpResponse::success('ok');
                }
            },
            action: 'index'
        );

        $payload = $response->jsonSerialize();

        self::assertSame('123e4567-e89b-12d3-a456-426614174000', $payload['request_id']);
    }

    public function testRequestIdIsOmittedWhenLoggerIsDisabled(): void
    {
        putenv('BFR_API_LOG_DRIVER=none');
        Logger::resetRequestId();

        $response = Request::run(
            controller: new class implements Controller {
                public function index(): HttpResponse
                {
                    return HttpResponse::success('ok');
                }
            },
            action: 'index'
        );

        $payload = $response->jsonSerialize();

        self::assertArrayNotHasKey('request_id', $payload);

        $property = new ReflectionProperty(Logger::class, 'requestId');
        $property->setAccessible(true);

        self::assertNull($property->getValue());
    }
}
