<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;
use Bifrost\Framework\Application;
use Bifrost\Framework\Container;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\Storage;
use InvalidArgumentException;

final class S3StorageExtension implements Extension
{
    private readonly S3ClientConfig $clientConfig;

    public function __construct(
        private readonly array $config,
        private readonly ?S3Client $client = null,
        private readonly ?S3ClientFactory $clientFactory = null
    ) {
        $this->clientConfig = S3ClientConfig::fromStorageConfig($config);
    }

    public function register(Application $application): void
    {
        $clientFactory = $this->clientFactory;
        if ($clientFactory === null && $this->client !== null) {
            $clientFactory = new FixedS3ClientFactory($this->client);
        }

        S3ServiceRegistrar::register($application, $clientFactory);

        $application->container()->bind(
            Storage::class,
            fn (Container $container): S3Storage => new S3Storage(
                client: $container->get(S3ClientFactory::class)->client($this->clientConfig),
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
}
