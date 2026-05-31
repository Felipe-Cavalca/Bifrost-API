<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3\Contracts;

use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\S3ClientConfig;

/**
 * Cria clientes S3 para extensoes opcionais.
 */
interface S3ClientFactory
{
    /**
     * Retorna um cliente S3 para a configuracao informada.
     */
    public function client(S3ClientConfig $config): S3Client;
}
