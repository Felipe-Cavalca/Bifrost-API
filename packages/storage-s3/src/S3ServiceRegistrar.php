<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Bifrost\Extension\StorageS3\Contracts\S3ClientFactory;
use Bifrost\Framework\Application;

/**
 * Garante um unico registro de cliente S3 compartilhado entre extensoes.
 */
final class S3ServiceRegistrar
{
    public static function register(Application $application, ?S3ClientFactory $factory = null): void
    {
        if ($application->container()->has(S3ClientFactory::class)) {
            return;
        }

        $application->container()->bind(
            S3ClientFactory::class,
            new S3ClientManager($factory ?? new NativeS3ClientFactory())
        );
    }
}
