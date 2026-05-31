<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogStdout;

use InvalidArgumentException;

final class StdoutLogConfig
{
    private function __construct(private readonly string $stream)
    {
    }

    public static function fromArray(array $config = []): self
    {
        $stream = $config['stream'] ?? 'stdout';
        if (!is_string($stream) || !in_array($stream, ['stdout', 'stderr'], true)) {
            throw new InvalidArgumentException('O stream de log deve ser stdout ou stderr.');
        }

        return new self($stream);
    }

    public function stream(): string
    {
        return $this->stream;
    }
}
