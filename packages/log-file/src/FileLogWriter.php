<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogFile;

use Bifrost\Extension\LogFile\Contracts\LogWriter;
use JsonException;
use RuntimeException;

final class FileLogWriter implements LogWriter
{
    /**
     * @param FileLogConfig $config Configuracao do arquivo de destino.
     */
    public function __construct(private readonly FileLogConfig $config)
    {
    }

    /**
     * @throws JsonException
     */
    public function write(array $entry): void
    {
        $directory = dirname($this->config->path());
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Nao foi possivel criar o diretorio de logs.');
        }

        $encoded = json_encode($entry, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->config->path(), $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
