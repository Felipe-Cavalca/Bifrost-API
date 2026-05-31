<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogStdout;

use Bifrost\Extension\LogStdout\Contracts\LogWriter;
use Closure;
use JsonException;

final class StdoutLogWriter implements LogWriter
{
    /** @var Closure(string): void */
    private readonly Closure $writeLine;

    public function __construct(StdoutLogConfig $config, ?callable $writeLine = null)
    {
        $this->writeLine = Closure::fromCallable(
            $writeLine ?? static function (string $line) use ($config): void {
                $target = $config->stream() === 'stderr' ? 'php://stderr' : 'php://stdout';
                file_put_contents($target, $line . PHP_EOL);
            }
        );
    }

    /**
     * @throws JsonException
     */
    public function write(array $entry): void
    {
        ($this->writeLine)(json_encode($entry, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
