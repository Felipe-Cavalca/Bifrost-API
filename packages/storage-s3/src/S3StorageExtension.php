<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\S3\S3Client;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\Storage;
use InvalidArgumentException;

final class S3StorageExtension implements Extension
{
    public function __construct(
        private readonly array $config,
        private readonly ?S3Client $client = null
    ) {
    }

    public function register(Application $application): void
    {
        $application->container()->bind(
            Storage::class,
            fn (): S3Storage => new S3Storage(
                client: $this->client ?? new S3Client($this->clientConfig()),
                bucket: $this->bucket()
            )
        );
    }

    private function bucket(): string
    {
        $bucket = $this->config['bucket'] ?? null;

        if (!is_string($bucket) || trim($bucket) === '') {
            throw new InvalidArgumentException('A configuracao S3 deve informar bucket.');
        }

        return $bucket;
    }

    private function clientConfig(): array
    {
        $config = $this->config;
        unset($config['bucket']);
        $config['version'] ??= 'latest';
        $config['region'] ??= 'us-east-1';

        return $config;
    }
}
