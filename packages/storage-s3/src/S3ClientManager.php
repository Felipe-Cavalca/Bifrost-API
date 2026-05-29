<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;

/**
 * Reutiliza clientes S3 por configuracao.
 */
final class S3ClientManager implements S3ClientFactory
{
    /** @var array<string, S3Client> */
    private array $clients = [];

    public function __construct(
        private readonly S3ClientFactory $factory = new NativeS3ClientFactory()
    ) {
    }

    public function client(S3ClientConfig $config): S3Client
    {
        $key = $config->fingerprint();

        return $this->clients[$key] ??= $this->factory->client($config);
    }
}
