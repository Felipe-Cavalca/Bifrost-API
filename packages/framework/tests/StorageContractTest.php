<?php

declare(strict_types=1);

namespace Bifrost\Framework\Tests;

use Bifrost\Framework\Contracts\Storage;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StorageContractTest extends TestCase
{
    public function testDefinesProviderIndependentStorageOperations(): void
    {
        $storage = new class implements Storage {
            public function put(string $key, string $body, array $options = []): array
            {
                return ['Key' => $key, 'ContentLength' => strlen($body)];
            }

            public function get(string $key, array $options = []): array
            {
                return ['Key' => $key, 'Body' => 'content'];
            }

            public function delete(string $key, array $options = []): array
            {
                return ['Key' => $key, 'Deleted' => true];
            }

            public function temporaryUrl(
                string $key,
                ?DateTimeImmutable $expiresAt = null,
                array $options = []
            ): string {
                return 'https://storage.example/' . $key;
            }
        };

        self::assertSame(7, $storage->put('reports/file.txt', 'content')['ContentLength']);
        self::assertSame('content', $storage->get('reports/file.txt')['Body']);
        self::assertTrue($storage->delete('reports/file.txt')['Deleted']);
        self::assertSame(
            'https://storage.example/reports/file.txt',
            $storage->temporaryUrl('reports/file.txt', new DateTimeImmutable('+5 minutes'))
        );
    }
}
