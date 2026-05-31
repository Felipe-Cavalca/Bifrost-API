<?php

declare(strict_types=1);

namespace Bifrost\Extension\StorageS3;

/**
 * Configuracao do cliente S3.
 */
final class S3ClientConfig
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(private readonly array $options)
    {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromStorageConfig(array $config): self
    {
        unset($config['bucket']);
        $config['version'] ??= 'latest';
        $config['region'] ??= 'us-east-1';

        return new self($config);
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Chave interna usada para reutilizar clientes equivalentes.
     */
    public function fingerprint(): string
    {
        return hash('sha256', serialize($this->normalizedValue($this->options)));
    }

    private function normalizedValue(mixed $value): mixed
    {
        if (is_array($value)) {
            ksort($value);

            return array_map(fn (mixed $item): mixed => $this->normalizedValue($item), $value);
        }

        if (is_object($value)) {
            return [
                'object' => $value::class,
                'id' => spl_object_id($value),
            ];
        }

        if (is_resource($value)) {
            return [
                'resource' => get_resource_type($value),
                'id' => (int) $value,
            ];
        }

        return $value;
    }
}
