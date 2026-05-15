<?php

declare(strict_types=1);

namespace Bifrost\Core;

class SessionConfig
{
    public function __construct(
        private readonly string $handler,
        private readonly string $savePath
    ) {
    }

    public static function fromRuntime(): self
    {
        return new self(
            handler: session_module_name(),
            savePath: session_save_path()
        );
    }

    public function shouldEnsureSavePath(): bool
    {
        return $this->handler === 'files'
            && $this->resolvedSavePath() !== null;
    }

    public function resolvedSavePath(): ?string
    {
        if ($this->savePath === '' || str_contains($this->savePath, '://')) {
            return null;
        }

        $parts = explode(';', $this->savePath);
        $path = end($parts);

        if (!is_string($path) || $path === '') {
            return null;
        }

        return $path;
    }
}
