<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\S3\S3Client;
use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;

/**
 * Factory baseada no cliente oficial da AWS.
 */
final class NativeS3ClientFactory implements S3ClientFactory
{
    public function client(S3ClientConfig $config): S3Client
    {
        return new S3Client($config->options());
    }
}
