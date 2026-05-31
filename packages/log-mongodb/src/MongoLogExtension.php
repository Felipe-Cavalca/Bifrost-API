<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogMongoDb;

use Bifrost\Extension\LogMongoDb\Contracts\LogWriter;
use Bifrost\Framework\Application;
use Bifrost\Framework\Contracts\Extension;
use Bifrost\Framework\Contracts\LogWriter as FrameworkLogWriter;
use Bifrost\Framework\Logging\Logger;
use Closure;

final class MongoLogExtension implements Extension
{
    private readonly MongoLogConfig $config;

    /** @var Closure(MongoLogConfig): LogWriter|null */
    private readonly ?Closure $writerFactory;

    public function __construct(array $config, ?callable $writerFactory = null)
    {
        $this->config = MongoLogConfig::fromArray($config);
        $this->writerFactory = $writerFactory === null
            ? null
            : Closure::fromCallable($writerFactory);
    }

    public function register(Application $application): void
    {
        $application->container()->bind(
            LogWriter::class,
            fn (): LogWriter => $this->createWriter()
        );
        $application->container()->bind(
            FrameworkLogWriter::class,
            fn (): FrameworkLogWriter => $application->container()->get(LogWriter::class)
        );
        $application->container()->bind(
            Logger::class,
            fn (): Logger => new Logger($application->container()->get(LogWriter::class))
        );
    }

    private function createWriter(): LogWriter
    {
        if ($this->writerFactory !== null) {
            return ($this->writerFactory)($this->config);
        }

        return MongoLogWriter::connect($this->config);
    }
}
