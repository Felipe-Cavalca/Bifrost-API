<?php

declare(strict_types=1);

namespace App\Worker;

use Bifrost\Extension\QueueWorker\TaskHandler;
use Bifrost\Extension\QueueWorker\TaskPayload;

final class ExampleTaskHandler implements TaskHandler
{
    public function handle(TaskPayload $payload): void
    {
        fwrite(STDOUT, sprintf("Tarefa %s processada.\n", $payload->task()));
    }
}
