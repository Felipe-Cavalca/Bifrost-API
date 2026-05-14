<?php

declare(strict_types=1);

use Bifrost\Core\Cache;
use Bifrost\Core\Settings;
use Bifrost\Integration\S3Storage;
use PHPUnit\Framework\TestCase;

final class RedisAndS3Test extends TestCase
{
    public function testRedisCacheFallsBackWhenDisabled(): void
    {
        $cache = new Cache();

        self::assertFalse($cache->set('x', 'y'));
        self::assertSame('fallback', $cache->get('x', 'fallback'));
        self::assertFalse($cache->exists('x'));
        self::assertFalse($cache->del('x'));
    }

    public function testS3StorageBuildsConfigFromConfiguredEnvironment(): void
    {
        if (getenv('BFR_API_S3_BUCKET') === false || getenv('BFR_API_S3_REGION') === false) {
            self::markTestSkipped('S3 nao configurado no ambiente.');
        }

        $settings = new Settings();

        $method = new ReflectionMethod(S3Storage::class, 'buildConfigFromSettings');
        $method->setAccessible(true);
        $config = $method->invoke(null, $settings);

        self::assertSame(getenv('BFR_API_S3_BUCKET'), $config['bucket']);
        self::assertSame(getenv('BFR_API_S3_REGION'), $config['region']);

        if (getenv('BFR_API_S3_ENDPOINT') !== false) {
            self::assertSame(getenv('BFR_API_S3_ENDPOINT'), $config['endpoint']);
        }

        if (getenv('BFR_API_S3_PATH_STYLE') !== false) {
            self::assertSame(
                filter_var(getenv('BFR_API_S3_PATH_STYLE'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
                $config['use_path_style_endpoint']
            );
        }

        self::assertInstanceOf(S3Storage::class, S3Storage::fromSettings($settings));
    }

    public function testS3StorageBuildsConfigFromTestEnvironment(): void
    {
        putenv('BFR_API_S3_BUCKET=bucket');
        putenv('BFR_API_S3_REGION=sa-east-1');
        putenv('BFR_API_S3_KEY=key');
        putenv('BFR_API_S3_SECRET=secret');
        putenv('BFR_API_S3_ENDPOINT=https://s3.example.com');
        putenv('BFR_API_S3_PATH_STYLE=true');

        $settings = new Settings();
        $method = new ReflectionMethod(S3Storage::class, 'buildConfigFromSettings');
        $method->setAccessible(true);
        $config = $method->invoke(null, $settings);

        self::assertSame('bucket', $config['bucket']);
        self::assertSame('sa-east-1', $config['region']);
        self::assertSame('https://s3.example.com', $config['endpoint']);
        self::assertTrue($config['use_path_style_endpoint']);

        putenv('BFR_API_S3_BUCKET');
        putenv('BFR_API_S3_REGION');
        putenv('BFR_API_S3_KEY');
        putenv('BFR_API_S3_SECRET');
        putenv('BFR_API_S3_ENDPOINT');
        putenv('BFR_API_S3_PATH_STYLE');
    }
}
