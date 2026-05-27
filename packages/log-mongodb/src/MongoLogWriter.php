<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogMongoDb;

use Bifrost\Extension\LogMongoDb\Contracts\LogWriter;
use Closure;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;
use RuntimeException;

final class MongoLogWriter implements LogWriter
{
    /** @var Closure(string, array): void */
    private readonly Closure $insertDocument;

    public function __construct(
        private readonly MongoLogConfig $config,
        callable $insertDocument
    ) {
        $this->insertDocument = Closure::fromCallable($insertDocument);
    }

    public static function connect(MongoLogConfig $config): self
    {
        if (!class_exists(Manager::class)) {
            throw new RuntimeException('A extensao PHP mongodb nao esta disponivel.');
        }

        $manager = new Manager($config->uri());

        return new self(
            config: $config,
            insertDocument: static function (string $namespace, array $entry) use ($manager): void {
                $bulk = new BulkWrite();
                $bulk->insert($entry);

                $manager->executeBulkWrite(
                    $namespace,
                    $bulk,
                    [
                        'writeConcern' => new WriteConcern(WriteConcern::MAJORITY, 1000),
                    ]
                );
            }
        );
    }

    public function write(array $entry): void
    {
        ($this->insertDocument)($this->config->collectionNamespace(), $entry);
    }
}
