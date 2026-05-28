<?php

declare(strict_types=1);

namespace Bifrost\Extension\QueueWorker;

interface TaskHandler
{
    public function handle(TaskPayload $payload): void;
}
