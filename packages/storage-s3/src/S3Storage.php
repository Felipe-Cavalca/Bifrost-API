<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Bifrost\Framework\Contracts\Storage;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class S3Storage implements Storage
{
    public function __construct(
        private readonly S3Client $client,
        private readonly string $bucket
    ) {
        if (trim($this->bucket) === '') {
            throw new InvalidArgumentException('O bucket S3 e obrigatorio.');
        }
    }

    public function put(string $key, string $body, array $options = []): array
    {
        return $this->run('putObject', array_merge(
            $options,
            ['Bucket' => $this->bucket, 'Key' => $this->normalizedKey($key), 'Body' => $body]
        ));
    }

    public function get(string $key, array $options = []): array
    {
        return $this->run('getObject', array_merge(
            $options,
            ['Bucket' => $this->bucket, 'Key' => $this->normalizedKey($key)]
        ));
    }

    public function delete(string $key, array $options = []): array
    {
        return $this->run('deleteObject', array_merge(
            $options,
            ['Bucket' => $this->bucket, 'Key' => $this->normalizedKey($key)]
        ));
    }

    public function temporaryUrl(
        string $key,
        ?DateTimeImmutable $expiresAt = null,
        array $options = []
    ): string {
        $expiresAt ??= new DateTimeImmutable('+15 minutes');

        if ($expiresAt <= new DateTimeImmutable()) {
            throw new InvalidArgumentException('A expiracao da URL S3 deve estar no futuro.');
        }

        $command = $this->client->getCommand('GetObject', array_merge(
            $options,
            ['Bucket' => $this->bucket, 'Key' => $this->normalizedKey($key)]
        ));
        $request = $this->client->createPresignedRequest($command, $expiresAt);

        return (string) $request->getUri();
    }

    public function client(): S3Client
    {
        return $this->client;
    }

    private function run(string $method, array $arguments): array
    {
        try {
            return $this->client->{$method}($arguments)->toArray();
        } catch (AwsException $exception) {
            throw new RuntimeException(
                'Nao foi possivel executar a operacao no storage S3: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function normalizedKey(string $key): string
    {
        $normalizedKey = str_replace('\\', '/', trim($key));

        if ($normalizedKey === '' || str_starts_with($normalizedKey, '/')) {
            throw new InvalidArgumentException('A chave de storage e invalida.');
        }

        foreach (explode('/', $normalizedKey) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..' || str_contains($segment, "\0")) {
                throw new InvalidArgumentException('A chave de storage e invalida.');
            }
        }

        return $normalizedKey;
    }
}
