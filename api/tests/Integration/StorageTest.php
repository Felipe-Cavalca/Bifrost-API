<?php

declare(strict_types=1);

use Bifrost\Core\Settings;
use Bifrost\Integration\Storage\LocalStorage;
use Bifrost\Integration\Storage\S3Storage as NamespacedS3Storage;
use Bifrost\Integration\Storage\StorageFactory;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
    private string $rootPath;

    protected function setUp(): void
    {
        $this->rootPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bifrost-storage-' . bin2hex(random_bytes(4));
        putenv('BFR_API_STORAGE_DRIVER');
        putenv('BFR_API_STORAGE_LOCAL_PATH');
    }

    protected function tearDown(): void
    {
        putenv('BFR_API_STORAGE_DRIVER');
        putenv('BFR_API_STORAGE_LOCAL_PATH');
        $this->removeDirectory($this->rootPath);
    }

    public function testLocalStorageWritesReadsAndDeletesFiles(): void
    {
        $storage = new LocalStorage($this->rootPath);

        $putResult = $storage->put('reports/example.txt', 'content');
        $getResult = $storage->get('reports/example.txt');
        $deleteResult = $storage->delete('reports/example.txt');

        self::assertSame('reports/example.txt', $putResult['Key']);
        self::assertSame(7, $putResult['ContentLength']);
        self::assertSame('content', $getResult['Body']);
        self::assertSame(7, $getResult['ContentLength']);
        self::assertTrue($deleteResult['Deleted']);
    }

    public function testLocalStorageRejectsPathTraversal(): void
    {
        $storage = new LocalStorage($this->rootPath);

        $this->expectException(InvalidArgumentException::class);
        $storage->put('../outside.txt', 'content');
    }

    public function testStorageFactoryUsesLocalStorageByDefault(): void
    {
        putenv('BFR_API_STORAGE_LOCAL_PATH=' . $this->rootPath);

        self::assertInstanceOf(LocalStorage::class, StorageFactory::fromSettings(new Settings()));
    }

    public function testStorageFactoryUsesConfiguredLocalVolumePath(): void
    {
        putenv('BFR_API_STORAGE_LOCAL_PATH=' . $this->rootPath);

        $storage = StorageFactory::fromSettings(new Settings());
        $storage->put('volume/file.txt', 'volume');

        self::assertFileExists($this->rootPath . DIRECTORY_SEPARATOR . 'volume' . DIRECTORY_SEPARATOR . 'file.txt');
    }

    public function testStorageFactoryUsesS3WhenConfigured(): void
    {
        putenv('BFR_API_STORAGE_DRIVER=s3');
        putenv('BFR_API_S3_BUCKET=bucket');
        putenv('BFR_API_S3_REGION=us-east-1');
        putenv('BFR_API_S3_KEY=key');
        putenv('BFR_API_S3_SECRET=secret');
        putenv('BFR_API_S3_ENDPOINT=http://localhost:8333');
        putenv('BFR_API_S3_PATH_STYLE=true');

        self::assertInstanceOf(NamespacedS3Storage::class, StorageFactory::fromSettings(new Settings()));

        putenv('BFR_API_S3_BUCKET');
        putenv('BFR_API_S3_REGION');
        putenv('BFR_API_S3_KEY');
        putenv('BFR_API_S3_SECRET');
        putenv('BFR_API_S3_ENDPOINT');
        putenv('BFR_API_S3_PATH_STYLE');
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }
            unlink($item->getPathname());
        }

        rmdir($path);
    }
}
