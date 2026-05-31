<?php

declare(strict_types=1);

use Bifrost\Extension\StorageLocal\LocalStorage;
use Bifrost\Extension\StorageLocal\LocalStorageExtension;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Storage;
use PHPUnit\Framework\TestCase;

final class LocalStorageTest extends TestCase
{
    private string $rootPath;

    protected function setUp(): void
    {
        $this->rootPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bifrost-storage-local-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootPath);
    }

    public function testStoresReadsAndDeletesFile(): void
    {
        $storage = new LocalStorage($this->rootPath);

        self::assertSame(
            ['Key' => 'reports/example.txt', 'ContentLength' => 7],
            $storage->put('reports/example.txt', 'content')
        );
        self::assertSame('content', $storage->get('reports/example.txt')['Body']);
        self::assertTrue($storage->delete('reports/example.txt')['Deleted']);
        self::assertFileDoesNotExist($this->rootPath . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'example.txt');
    }

    public function testRejectsPathTraversal(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new LocalStorage($this->rootPath))->put('../outside.txt', 'content');
    }

    public function testRegistersStorageContract(): void
    {
        $application = Application::create()->extend(new LocalStorageExtension($this->rootPath));

        self::assertInstanceOf(Storage::class, $application->container()->get(Storage::class));
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
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($path);
    }
}
