<?php

declare(strict_types=1);

use Aws\MockHandler;
use Aws\Result;
use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\S3Storage;
use Bifrost\Extension\StorageS3\S3StorageExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Storage;
use PHPUnit\Framework\TestCase;

final class S3StorageTest extends TestCase
{
    public function testExecutesObjectOperations(): void
    {
        $storage = new S3Storage($this->clientWithResults(
            new Result(['ETag' => '"etag"']),
            new Result(['Body' => 'content', 'ContentLength' => 7]),
            new Result([])
        ), 'uploads');

        self::assertSame('"etag"', $storage->put('reports/example.txt', 'content')['ETag']);
        self::assertSame('content', $storage->get('reports/example.txt')['Body']);
        self::assertIsArray($storage->delete('reports/example.txt'));
    }

    public function testCreatesTemporaryUrl(): void
    {
        $storage = new S3Storage($this->clientWithResults(), 'uploads');

        $url = $storage->temporaryUrl('reports/example.txt');

        self::assertStringContainsString('reports/example.txt', $url);
        self::assertStringContainsString('X-Amz-Signature=', $url);
    }

    public function testRegistersStorageContractWithInjectedClient(): void
    {
        $application = Application::create()->extend(new S3StorageExtension(
            config: ['bucket' => 'uploads'],
            client: $this->clientWithResults()
        ));

        self::assertInstanceOf(Storage::class, $application->container()->get(Storage::class));
    }

    private function clientWithResults(Result ...$results): S3Client
    {
        return new S3Client([
            'credentials' => ['key' => 'key', 'secret' => 'secret'],
            'handler' => new MockHandler($results),
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);
    }
}
