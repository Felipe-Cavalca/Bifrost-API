<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;

/**
 * Factory para clientes S3 ja criados pela aplicacao.
 */
final class FixedS3ClientFactory implements S3ClientFactory
{
    /**
     * @param S3Client $client Cliente que sempre sera retornado.
     */
    public function __construct(private readonly S3Client $client)
    {
    }

    /**
     * Retorna o cliente S3 informado no construtor.
     */
    public function client(S3ClientConfig $config): S3Client
    {
        return $this->client;
    }
}
