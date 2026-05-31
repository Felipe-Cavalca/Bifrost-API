<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogFile;

use InvalidArgumentException;

final class FileLogConfig
{
    private function __construct(private readonly string $path)
    {
    }

    /**
     * Cria a configuracao a partir de array.
     *
     * @param array{path?: string} $config
     */
    public static function fromArray(array $config): self
    {
        $path = $config['path'] ?? null;
        if (!is_string($path) || trim($path) === '') {
            throw new InvalidArgumentException('O caminho do arquivo de log e obrigatorio.');
        }

        return new self(trim($path));
    }

    /**
     * Retorna o caminho do arquivo de log.
     */
    public function path(): string
    {
        return $this->path;
    }
}
