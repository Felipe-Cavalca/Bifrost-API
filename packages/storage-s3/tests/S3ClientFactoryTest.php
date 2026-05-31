<?php

declare(strict_types=1);

use Aws\MockHandler;
use Aws\Result;
use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;
use Bifrost\Extension\StorageS3\S3ClientConfig;
use Bifrost\Extension\StorageS3\S3ClientManager;
use Bifrost\Extension\StorageS3\S3Storage;
use Bifrost\Extension\StorageS3\S3StorageExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Storage;
use PHPUnit\Framework\TestCase;

final class S3ClientFactoryTest extends TestCase
{
    public function testManagerReusesClientForSameConfig(): void
    {
        $factory = new CountingS3ClientFactory();
        $manager = new S3ClientManager($factory);
        $config = S3ClientConfig::fromStorageConfig(['bucket' => 'uploads']);

        $first = $manager->client($config);
        $second = $manager->client($config);

        self::assertSame($first, $second);
        self::assertSame(1, $factory->clients);
    }

    public function testExtensionUsesSharedClientFactory(): void
    {
        $factory = new CountingS3ClientFactory();
        $application = Application::create();
        $application->container()->instance(S3ClientFactory::class, $factory);

        $application->extend(new S3StorageExtension(['bucket' => 'uploads']));

        $storage = $application->container()->get(Storage::class);

        self::assertInstanceOf(S3Storage::class, $storage);
        self::assertSame(1, $factory->clients);
        self::assertSame('us-east-1', $factory->lastConfig?->options()['region']);
    }

    public function testExtensionKeepsInjectedClientCompatibility(): void
    {
        $client = $this->clientWithResults();
        $application = Application::create()->extend(new S3StorageExtension(
            config: ['bucket' => 'uploads'],
            client: $client
        ));

        $storage = $application->container()->get(Storage::class);

        self::assertInstanceOf(S3Storage::class, $storage);
        self::assertSame($client, $storage->client());
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

final class CountingS3ClientFactory implements S3ClientFactory
{
    public int $clients = 0;
    public ?S3ClientConfig $lastConfig = null;

    public function client(S3ClientConfig $config): S3Client
    {
        $this->clients++;
        $this->lastConfig = $config;

        return new S3Client([
            'credentials' => ['key' => 'key', 'secret' => 'secret'],
            'handler' => new MockHandler([new Result([])]),
            'region' => 'us-east-1',
            'version' => 'latest',
        ]);
    }
}
