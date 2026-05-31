<?php

declare(strict_types=1);

namespace Bifrost\Extension\LogMongoDb\Contracts;

interface LogWriter
{
    public function write(array $entry): void;
}
