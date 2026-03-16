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

    public function testS3StorageRequiresSdkAndBuildsConfigFromSettings(): void
    {
        $settings = new Settings();
        putenv('BFR_API_S3_BUCKET=bucket');
        putenv('BFR_API_S3_REGION=sa-east-1');
        putenv('BFR_API_S3_KEY=key');
        putenv('BFR_API_S3_SECRET=secret');
        putenv('BFR_API_S3_ENDPOINT=https://s3.example.com');
        putenv('BFR_API_S3_PATH_STYLE=true');

        $method = new ReflectionMethod(S3Storage::class, 'buildConfigFromSettings');
        $method->setAccessible(true);
        $config = $method->invoke(null, $settings);

        self::assertSame('bucket', $config['bucket']);
        self::assertSame('sa-east-1', $config['region']);
        self::assertSame('https://s3.example.com', $config['endpoint']);
        self::assertTrue($config['use_path_style_endpoint']);

        $this->expectException(RuntimeException::class);
        new S3Storage(['bucket' => 'bucket']);
    }
}
