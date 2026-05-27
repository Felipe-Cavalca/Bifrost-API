<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageLocal;

use Bifrost\Framework\Contracts\Storage;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class LocalStorage implements Storage
{
    public function __construct(private readonly string $rootPath)
    {
        if (trim($this->rootPath) === '') {
            throw new InvalidArgumentException('O diretorio raiz do storage local e obrigatorio.');
        }
    }

    public function put(string $key, string $body, array $options = []): array
    {
        $key = $this->normalizedKey($key);
        $path = $this->pathFor($key);
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Nao foi possivel criar o diretorio do storage local.');
        }

        if (file_put_contents($path, $body) === false) {
            throw new RuntimeException('Nao foi possivel gravar o arquivo no storage local.');
        }

        return ['Key' => $key, 'ContentLength' => strlen($body)];
    }

    public function get(string $key, array $options = []): array
    {
        $key = $this->normalizedKey($key);
        $path = $this->pathFor($key);

        if (!is_file($path)) {
            throw new RuntimeException('Arquivo nao encontrado no storage local.');
        }

        $body = file_get_contents($path);
        if ($body === false) {
            throw new RuntimeException('Nao foi possivel ler o arquivo do storage local.');
        }

        return ['Key' => $key, 'Body' => $body, 'ContentLength' => strlen($body)];
    }

    public function delete(string $key, array $options = []): array
    {
        $key = $this->normalizedKey($key);
        $path = $this->pathFor($key);

        return ['Key' => $key, 'Deleted' => !is_file($path) || unlink($path)];
    }

    public function temporaryUrl(
        string $key,
        ?DateTimeImmutable $expiresAt = null,
        array $options = []
    ): string {
        return 'file://' . $this->pathFor($this->normalizedKey($key));
    }

    private function pathFor(string $key): string
    {
        return rtrim($this->rootPath, '\\/')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $key);
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
